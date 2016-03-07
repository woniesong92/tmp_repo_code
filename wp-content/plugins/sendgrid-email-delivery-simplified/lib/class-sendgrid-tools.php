<?php

class Sendgrid_Tools
{
  const CACHE_GROUP = "sendgrid";
  const CHECK_CREDENTIALS_CACHE_KEY = "sendgrid_credentials_check";
  const CHECK_API_KEY_CACHE_KEY = "sendgrid_api_key_check";
  const VALID_CREDENTIALS_STATUS = "valid";
  const INVALID_CREDENTIALS_STATUS = "invalid";

  /**
   * Check username/password
   *
   * @param   string  $username   sendgrid username
   * @param   string  $password   sendgrid password
   * @return  bool
   */
  public static function check_username_password( $username, $password, $clear_cache = false )
  {
    if ( !$username or !$password )
      return false;

    if ( $clear_cache )
      wp_cache_delete(self::CHECK_CREDENTIALS_CACHE_KEY, self::CACHE_GROUP);

    $valid_username_password = wp_cache_get(self::CHECK_CREDENTIALS_CACHE_KEY, self::CACHE_GROUP);
    if ( self::VALID_CREDENTIALS_STATUS == $valid_username_password )
      return true;
    elseif ( self::INVALID_CREDENTIALS_STATUS == $valid_username_password )
      return false;

    $url = 'https://api.sendgrid.com/api/profile.get.json?';
    $url .= "api_user=" . urlencode($username) . "&api_key=" . urlencode($password);

    $response = wp_remote_get( $url );
    
    if ( !is_array($response) or !isset( $response['body'] ) )
    {
      wp_cache_set(self::CHECK_CREDENTIALS_CACHE_KEY, self::INVALID_CREDENTIALS_STATUS, self::CACHE_GROUP, 60);

      return false;
    }

    $response = json_decode( $response['body'], true );

    if ( isset( $response['error'] ) )
    {
      wp_cache_set(self::CHECK_CREDENTIALS_CACHE_KEY, self::INVALID_CREDENTIALS_STATUS, self::CACHE_GROUP, 60);

      return false;
    }

    wp_cache_set(self::CHECK_CREDENTIALS_CACHE_KEY, self::VALID_CREDENTIALS_STATUS, self::CACHE_GROUP, 1800);

    return true;
  }

  /**
   * Check apikey
   *
   * @param   string  $apikey   sendgrid apikey
   * @return  bool
   */
  public static function check_api_key( $apikey, $clear_cache = false )
  {
    if ( ! $apikey ) 
      return false;

    if ( $clear_cache )
      wp_cache_delete(self::CHECK_API_KEY_CACHE_KEY, self::CACHE_GROUP);

    $valid_apikey = wp_cache_get(self::CHECK_API_KEY_CACHE_KEY, self::CACHE_GROUP);
    if ( self::VALID_CREDENTIALS_STATUS == $valid_apikey )
      return true;
    elseif ( self::INVALID_CREDENTIALS_STATUS == $valid_apikey )
      return false;

    $url = 'https://api.sendgrid.com/api/mail.send.json';

    $args = array(
      'headers' => array(
        'Authorization' => 'Bearer ' . $apikey )
    );

    $response = wp_remote_get( $url, $args );

    if ( ! is_array( $response ) or ! isset( $response['body'] ) ) {
      wp_cache_set(self::CHECK_API_KEY_CACHE_KEY, self::INVALID_CREDENTIALS_STATUS, self::CACHE_GROUP, 60);

      return false;
    }

    $response = json_decode( $response['body'], true );

    if ( isset( $response['errors'] ) and 
      ( ( 'Authenticated user is not authorized to send mail' == $response['errors'][0] ) or
      ( 'The provided authorization grant is invalid, expired, or revoked' == $response['errors'][0] ) ) ) {
      wp_cache_set(self::CHECK_API_KEY_CACHE_KEY, self::INVALID_CREDENTIALS_STATUS, self::CACHE_GROUP, 60);

      return false;
    }

    wp_cache_set(self::CHECK_API_KEY_CACHE_KEY, self::VALID_CREDENTIALS_STATUS, self::CACHE_GROUP, 1800);

    return true;
  }

  /**
   * Check template
   *
   * @param   string  $template   sendgrid template
   * @return  bool
   */
  public static function check_template( $template )
  {
    if ( '' == $template )
      return true;

    $url = 'v3/templates/' . $template;

    $parameters['auth_method'] = Sendgrid_Tools::get_auth_method();
    $parameters['api_username'] = Sendgrid_Tools::get_username();
    $parameters['api_password']  = Sendgrid_Tools::get_password();
    $parameters['apikey']   = Sendgrid_Tools::get_api_key();

    $response = Sendgrid_Tools::do_request( $url, $parameters );

    if ( ! $response ) 
      return false;

    $response = json_decode( $response, true );
    if ( isset( $response['error'] ) or ( isset( $response['errors'] ) && isset( $response['errors'][0]['message'] ) ) )
      return false;

    return true;
  }

  /**
   * Make request to SendGrid API
   *
   * @param type $api
   * @param type $parameters
   * @return json
   */
  public static function do_request( $api = 'v3/stats', $parameters = array() )
  {
    $args = array();
    if ( "credentials" == $parameters['auth_method'] ) {
      $creds = base64_encode($parameters['api_username'] . ':' . $parameters['api_password']);

      $args = array(
        'headers' => array(
          'Authorization' => 'Basic ' . $creds 
        )
      );

    } else {
      $args = array(
        'headers' => array(
          'Authorization' => 'Bearer ' . $parameters['apikey'] 
        )
      );
    }

    unset($parameters['auth_method']);
    unset($parameters['api_username']);
    unset($parameters['api_password']);
    unset($parameters['apikey']);

    $data = urldecode( http_build_query( $parameters ) );
    $url = "https://api.sendgrid.com/$api?$data";

    $response = wp_remote_get( $url, $args );

    if ( !is_array($response) or !isset( $response['body'] ) )
    {
      return false;
    }

    return $response['body'];
  }

  /**
   * Return username from the database or global variable
   *
   * @return string username
   */
  public static function get_username()
  {
    if ( defined('SENDGRID_USERNAME') ) {
      return SENDGRID_USERNAME;
    } else {
      $username = get_option('sendgrid_user');
      if( $username ) {
        delete_option('sendgrid_user');
        update_option('sendgrid_username', $username);
      }

      return get_option('sendgrid_username');
    }
  }

  /**
   * Sets username in the database
   * @param type string $username
   * @return bool
   */
  public static function set_username($username)
  {
    if( ! isset( $username ) )
      return update_option('sendgrid_username', '');

    return update_option('sendgrid_username', $username);
  }

  /**
   * Return password from the database or global variable
   *
   * @return string password
   */
  public static function get_password()
  {
    if ( defined('SENDGRID_PASSWORD') ) {
      return SENDGRID_PASSWORD;
    } else {
      $password = get_option('sendgrid_pwd');
      $new_password = get_option('sendgrid_password');
      if( $new_password && ! $password ) {
        update_option('sendgrid_pwd', self::decrypt( $new_password, AUTH_KEY ) );
        delete_option('sendgrid_password');
      }

      $password = get_option('sendgrid_pwd');
      return $password;
    }
  }

  /**
   * Sets password in the database
   * @param type string $password
   * @return bool
   */
  public static function set_password($password)
  {
    return update_option('sendgrid_pwd', $password);
  }

  /**
   * Return api_key from the database or global variable
   *
   * @return string api key
   */
  public static function get_api_key()
  {
    if ( defined('SENDGRID_API_KEY') ) {
      return SENDGRID_API_KEY;
    } else {
      $apikey = get_option('sendgrid_api_key');
      $new_apikey = get_option('sendgrid_apikey');
      if( $new_apikey && ! $apikey ) {
        update_option('sendgrid_api_key', self::decrypt( $new_apikey, AUTH_KEY ));
        delete_option('sendgrid_apikey');
      }

      $apikey = get_option('sendgrid_api_key');
      return $apikey;
    }
  }

  /**
   * Sets api_key in the database
   * @param type string $apikey
   * @return bool
   */
  public static function set_api_key($apikey)
  {
    return update_option('sendgrid_api_key', $apikey);
  }

  /**
   * Return send method from the database or global variable
   *
   * @return string send_method
   */
  public static function get_send_method()
  {
    if ( defined('SENDGRID_SEND_METHOD') ) {
      return SENDGRID_SEND_METHOD;
    } elseif ( get_option('sendgrid_api') ) {
      return get_option('sendgrid_api');
    } else {
      return 'api';
    }
  }

  /**
   * Return auth method from the database or global variable
   *
   * @return string auth_method
   */
  public static function get_auth_method()
  {
    if ( defined('SENDGRID_AUTH_METHOD') ) {
      return SENDGRID_AUTH_METHOD;
    } elseif ( get_option('sendgrid_auth_method') ) {
      $auth_method = get_option('sendgrid_auth_method');
      if ( 'username' == $auth_method ) {
        $auth_method = 'credentials';
        update_option('sendgrid_auth_method', $auth_method);
      }

      return $auth_method;
    } elseif ( Sendgrid_Tools::get_api_key() ) {
      return 'apikey';
    } elseif ( Sendgrid_Tools::get_username() and Sendgrid_Tools::get_password() ) {
      return 'credentials';
    } else {
      return 'apikey';
    }
  }

  /**
   * Return port from the database or global variable
   *
   * @return string port
   */
  public static function get_port()
  {
    if ( defined('SENDGRID_PORT') ) {
      return SENDGRID_PORT;
    } else {
      return get_option('sendgrid_port');
    }
  }

  /**
   * Return from name from the database or global variable
   *
   * @return string from_name
   */
  public static function get_from_name()
  {
    if ( defined('SENDGRID_FROM_NAME') ) {
      return SENDGRID_FROM_NAME;
    } else {
      return get_option('sendgrid_from_name');
    }
  }

  /**
   * Return from email address from the database or global variable
   *
   * @return string from_email
   */
  public static function get_from_email()
  {
    if ( defined('SENDGRID_FROM_EMAIL') ) {
      return SENDGRID_FROM_EMAIL;
    } else {
      return get_option('sendgrid_from_email');
    }
  }

  /**
   * Return reply to email address from the database or global variable
   *
   * @return string reply_to
   */
  public static function get_reply_to()
  {
    if ( defined('SENDGRID_REPLY_TO') ) {
      return SENDGRID_REPLY_TO;
    } else {
      return get_option('sendgrid_reply_to');
    }
  }

  /**
   * Return categories from the database or global variable
   *
   * @return string categories
   */
  public static function get_categories()
  {
    if ( defined('SENDGRID_CATEGORIES') ) {
      return SENDGRID_CATEGORIES;
    } else {
      return get_option('sendgrid_categories');
    }
  }

  /**
   * Return categories array
   *
   * @return array categories
   */
  public static function get_categories_array()
  {
    $categories = Sendgrid_Tools::get_categories();
    if ( strlen( trim( $categories ) ) )
    {
      return explode( ',', $categories );
    }

    return array();
  }

  /**
   * Return template from the database or global variable
   *
   * @return string template
   */
  public static function get_template()
  {
    if ( defined('SENDGRID_TEMPLATE') ) {
      return SENDGRID_TEMPLATE;
    } else {
      return get_option( 'sendgrid_template' );
    }
  }

  /**
   * Returns decrypted string using the key or empty string in case of error
   *
   * @return string template
   */
  private static function decrypt($encrypted_input_string, $key) {
    if (!extension_loaded('mcrypt')) {
      return '';
    }

    $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
    if(false === $iv_size)
      return '';

    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
    if(false === $iv)
      return '';

    $h_key = hash('sha256', $key, TRUE);
    $decoded = base64_decode($encrypted_input_string);
    if(false === $decoded)
      return '';

    $decrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $h_key, $decoded, MCRYPT_MODE_ECB, $iv);
    if(false === $decrypted)
      return '';

    return trim($decrypted);
  }

  /**
   * Check apikey stats permissions
   *
   * @param   string  $apikey   sendgrid apikey
   * @return  bool
   */
  public static function check_api_key_stats( $apikey )
  {
    $url = 'https://api.sendgrid.com/v3/stats';

    $args = array(
      'headers' => array(
        'Authorization' => 'Bearer ' . $apikey )
    );

    $response = wp_remote_get( $url, $args );

    if ( ! is_array( $response ) or ! isset( $response['body'] ) ) {
      return false;
    }

    $response = json_decode( $response['body'], true );

    if ( isset( $response['errors'] ) and ( 'access forbidden' == $response['errors'][0]['message'] ) ) {
      return false;
    }

    return true;
  }
}