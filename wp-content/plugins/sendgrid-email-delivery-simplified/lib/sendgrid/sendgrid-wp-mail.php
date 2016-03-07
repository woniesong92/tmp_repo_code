<?php

require_once plugin_dir_path( __FILE__ ) . '../../vendor/autoload.php';
require_once plugin_dir_path( __FILE__ ) . 'class-sendgrid-php.php';

/**
 * Send mail, similar to PHP's mail
 *
 * A true return value does not automatically mean that the user received the
 * email successfully. It just only means that the method used was able to
 * process the request without any errors.
 *
 * Using the two 'wp_mail_from' and 'wp_mail_from_name' hooks allow from
 * creating a from address like 'Name <email@address.com>' when both are set. If
 * just 'wp_mail_from' is set, then just the email address will be used with no
 * name.
 *
 * The default content type is 'text/plain' which does not allow using HTML.
 * However, you can set the content type of the email by using the
 * 'wp_mail_content_type' filter.
 *
 * The default charset is based on the charset used on the blog. The charset can
 * be set using the 'wp_mail_charset' filter.
 *
 * @since 1.2.1
 * @uses apply_filters() Calls 'wp_mail' hook on an array of all of the parameters.
 * @uses apply_filters() Calls 'wp_mail_from' hook to get the from email address.
 * @uses apply_filters() Calls 'wp_mail_from_name' hook to get the from address name.
 * @uses apply_filters() Calls 'wp_mail_content_type' hook to get the email content type.
 * @uses apply_filters() Calls 'wp_mail_charset' hook to get the email charset
 *
 * @param   string|array  $to           Array or comma-separated list of email addresses to send message.
 * @param   string        $subject      Email subject
 * @param   string        $message      Message contents
 * @param   string|array  $headers      Optional. Additional headers.
 * @param   string|array  $attachments  Optional. Files to attach.
 * @return  bool                        Whether the email contents were sent successfully.
 */
function wp_mail( $to, $subject, $message, $headers = '', $attachments = array() )
{
  $mail     = new SendGrid\Email();

  $method   = Sendgrid_Tools::get_send_method();

  // Compact the input, apply the filters, and extract them back out
  extract( apply_filters( 'wp_mail', compact( 'to', 'subject', 'message', 'headers', 'attachments' ) ) );

  // prepare attachments
  $attached_files = array();
  if ( ! empty( $attachments ) ) {
    if ( ! is_array( $attachments ) ) {
      $pos = strpos( ',', $attachments );
      if ( false !== $pos ) {
        $attachments = preg_split( '/,\s*/', $attachments );
      } else {
        $attachments = explode( "\n", str_replace( "\r\n", "\n", $attachments ) );
      }
    }

    if ( is_array( $attachments ) ) {
      foreach ( $attachments as $attachment ) {
        if ( file_exists( $attachment ) ) {
          $attached_files[] = $attachment;
        }
      }
    }
  }

  $template = Sendgrid_Tools::get_template();
  if ( $template) {
    $mail->setTemplateId( $template );
  }

  // Headers
  $cc  = array();
  $bcc = array();
  $unique_args = array();
  if ( empty( $headers ) ) {
    $headers = array();
  } else {
    if ( ! is_array( $headers ) ) {
      // Explode the headers out, so this function can take both
      // string headers and an array of headers.
      $tempheaders = explode( "\n", str_replace( "\r\n", "\n", $headers ) );
    } else {
      $tempheaders = $headers;
    }
    $headers = array();

    // If it's actually got contents
    if ( ! empty( $tempheaders ) ) {
      // Iterate through the raw headers
      foreach ( (array) $tempheaders as $header ) {
        if ( false === strpos($header, ':') ) {
          if ( false !== stripos( $header, 'boundary=' ) ) {
            $parts = preg_split( '/boundary=/i', trim( $header ) );
            $boundary = trim( str_replace( array( "'", '"' ), '', $parts[1] ) );
          }
          continue;
        }
        // Explode them out
        list( $name, $content ) = explode( ':', trim( $header ), 2 );

        // Cleanup crew
        $name    = trim( $name    );
        $content = trim( $content );

        switch ( strtolower( $name ) ) {
          // Mainly for legacy -- process a From: header if it's there
          case 'from':
            if ( false !== strpos( $content, '<' ) ) {
              // So... making my life hard again?
              $from_name = substr( $content, 0, strpos( $content, '<' ) - 1 );
              $from_name = str_replace( '"', '', $from_name );
              $from_name = trim( $from_name );

              $from_email = substr( $content, strpos( $content, '<' ) + 1 );
              $from_email = str_replace( '>', '', $from_email );
              $from_email = trim( $from_email );
            } else {
              $from_email = trim( $content );
            }
            break;
          case 'content-type':
            if ( false !== strpos( $content, ';' ) ) {
              list( $type, $charset ) = explode( ';', $content );
              $content_type = trim( $type );
              if ( false !== stripos( $charset, 'charset=' ) ) {
                $charset = trim( str_replace( array( 'charset=', '"' ), '', $charset ) );
              } elseif ( false !== stripos( $charset, 'boundary=' ) ) {
                $boundary = trim( str_replace( array( 'BOUNDARY=', 'boundary=', '"' ), '', $charset ) );
                $charset = '';
              }
            } else {
              $content_type = trim( $content );
            }
            break;
          case 'cc':
            $cc = array_merge( (array) $cc, explode( ',', $content ) );
            foreach ( $cc as $key => $recipient ) {
              $cc[ $key ] = trim( $recipient );
            }
            break;
          case 'bcc':
            $bcc = array_merge( (array) $bcc, explode( ',', $content ) );
            foreach ( $bcc as $key => $recipient ) {
              $bcc[ $key ] = trim( $recipient );
            }
            break;
          case 'reply-to':
            $replyto = $content;
            break;
          case 'unique-args':
            if ( false !== strpos( $content, ';' ) ) {
              $unique_args = explode( ';', $content );
            }
            else {
              $unique_args = (array) trim( $content );
            }
            foreach ( $unique_args as $unique_arg ) {
              if ( false !== strpos( $content, '=' ) ) {
                list( $key, $val ) = explode( '=', $unique_arg );
                $mail->addUniqueArg( trim( $key ), trim( $val ) );
              } 
            }
            break;
          case 'template':
            $template_ok = Sendgrid_Tools::check_template( trim( $content ) );
            if ( $template_ok ) {
              $mail->setTemplateId( trim( $content ) );
            } elseif ( Sendgrid_Tools::get_template() ) {
              $mail->setTemplateId( Sendgrid_Tools::get_template() );
            }
            break;
          case 'categories':
            $categories = explode( ',', trim( $content ) );
            foreach ( $categories as $category ) {
              $mail->addCategory( $category );
            }
            break;
          default:
            // Add it to our grand headers array
            $headers[trim( $name )] = trim( $content );
            break;
        }
      }
    }
  }

  // From email and name
  // If we don't have a name from the input headers
  if ( !isset( $from_name ) )
    $from_name = Sendgrid_Tools::get_from_name();

  /* If we don't have an email from the input headers default to wordpress@$sitename
   * Some hosts will block outgoing mail from this address if it doesn't exist but
   * there's no easy alternative. Defaulting to admin_email might appear to be another
   * option but some hosts may refuse to relay mail from an unknown domain. See
   * http://trac.wordpress.org/ticket/5007.
   */

  if ( !isset( $from_email ) ) {
    $from_email = trim( Sendgrid_Tools::get_from_email() );
    if (!$from_email) {
      // Get the site domain and get rid of www.
      $sitename = strtolower( $_SERVER['SERVER_NAME'] );
      if ( 'www.' == substr( $sitename, 0, 4 ) ) {
        $sitename = substr( $sitename, 4 );
      }

      $from_email = "wordpress@$sitename";
    }
  }

  // Plugin authors can override the potentially troublesome default
  $from_email = apply_filters( 'wp_mail_from'     , $from_email );
  $from_name  = apply_filters( 'wp_mail_from_name', $from_name  );

  // Set destination addresses
  if ( !is_array( $to ) )
    $to = explode( ',', $to );

  // Add any CC and BCC recipients
  if ( ! empty( $cc ) ) {
    foreach ( (array) $cc as $key => $recipient ) {
      // Break $recipient into name and address parts if in the format "Foo <bar@baz.com>"
      if ( preg_match( '/(.*)<(.+)>/', $recipient, $matches ) ) {
        if ( count( $matches ) == 3 ) {
          $cc[ $key ] = trim( $matches[2] );
        }
      }
    }
  }

  if ( ! empty( $bcc ) ) {
    foreach ( (array) $bcc as $key => $recipient ) {
      // Break $recipient into name and address parts if in the format "Foo <bar@baz.com>"
      if( preg_match( '/(.*)<(.+)>/', $recipient, $matches ) ) {
        if ( 3 == count( $matches ) ) {
          $bcc[ $key ] = trim( $matches[2] );
        }
      }
    }
  }

  $toname = array();
  foreach ( (array) $to as $key => $recipient ) {
    // Break $recipient into name and address parts if in the format "Foo <bar@baz.com>"
    if ( preg_match(  '/(.*)<(.+)>/', $recipient, $matches ) ) {
      if ( 3 == count( $matches ) ) {
        $to[ $key ] = trim( $matches[2] );
        $toname[ $key ] = trim( $matches[1] );
      }
    }
  }

  // Set Content-Type and charset
  // If we don't have a content-type from the input headers
  if ( ! isset( $content_type ) )
    $content_type = 'text/plain';

  $content_type = apply_filters( 'wp_mail_content_type', $content_type );

  $mail->setSubject( $subject )
       ->setText( $message )
       ->addCategory( SENDGRID_CATEGORY )
       ->setFrom( $from_email );

  if ( 'api' == $method ) {
    $mail->addTo( $to , $toname );
  } else {
    $mail->addTo( $to );
  }

  $categories = explode( ',', Sendgrid_Tools::get_categories() );
  foreach ($categories as $category) {
    $mail->addCategory($category);
  }

  // send HTML content
  if ( 'text/plain' !== $content_type ) {
    $mail->setHtml( $message );
  }

  // set from name
  if ( $from_email ) {
    $mail->setFromName( $from_name );
  }

  // set from cc
  if ( count( $cc ) ) {
    $mail->setCcs( $cc );
  }

  // set from bcc
  if ( count( $bcc ) ) {
    $mail->setBccs( $bcc );
  }

  if ( ! isset( $replyto ) ) {
    $replyto = trim( Sendgrid_Tools::get_reply_to() );
  }

  $reply_to_found = preg_match( '/.*<(.*)>.*/i', $replyto, $result );
  if ( $reply_to_found ) {
    $replyto = $result[1];
  }

  $mail->setReplyTo( $replyto );
  
  // add attachemnts
  if ( count( $attached_files ) ) {
    $mail->setAttachments( $attached_files );
  }

  $sendgrid = Sendgrid_WP::get_instance();

  return $sendgrid->send($mail);
}
  

if ( ! function_exists('set_html_content_type') )
{
  /**
   * Return the content type used to send html emails
   *
   * return string Conteny-type needed to send HTML emails
   */
  function set_html_content_type()
  {
    return 'text/html';
  }
}
