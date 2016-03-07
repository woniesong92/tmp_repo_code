<?php
/**
 * The header for our theme.
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package onboardiq
 */

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="profile" href="http://gmpg.org/xfn/11">
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">

<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
  <div id="page" class="site">
    <nav class="navbar navbar-static-top navbar-custom" role="navigation">
        <div class="container">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <button type="button"  class="navbar-toggle" data-toggle="collapse" data-target="#main-collapse-nav">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand logo" href="/"></a>
            </div><!--end navbar-header-->
            <div class="collapse navbar-collapse" id="main-collapse-nav">
              <ul class="nav navbar-nav navbar-right">
                <li><a class="menu-item" href="https://onboardiq.com">Home</a></li>
                <li><a class="menu-item" href="https://onboardiq.com/features">Features</a></li>
                <li><a class="menu-item" href="https://onboardiq.com/faq">FAQ</a></li>
                <li><a class="menu-item menu-item-active" href="/">Blog</a></li>
                <li class="demo"><a href="https://www.onboardiq.com/request_demo">Request a Demo</a></li>
              </ul>
          </div>
        </div><!--end container-->
    </nav>
    <!-- Begin MailChimp Signup Form -->
    <div id="subscribe-header">
       <form action="//onboardiq.us12.list-manage.com/subscribe/post?u=92fa02f66d82bdb55a4381221&amp;id=9d5ccee144" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
          <div id="mc_embed_signup_scroll">
             <div class="subscribe-box">
                <h1>OnboardIQ Blog</h1>
                <div class="mc-field-group">
                   <input type="email" value="" name="EMAIL" placeholder="Email Address" class="subscribe-email" id="mce-EMAIL">
                   <button type="submit" name="subscribe" id="mc-embedded-subscribe" class="subscribe-button">Subscribe</button>
                </div>
                <div id="mce-responses" class="clear">
                   <div class="response" id="mce-error-response" style="display:none"></div>
                   <div class="response" id="mce-success-response" style="display:none"></div>
                </div>
                <!-- real people should not fill this in and expect good things - do not remove this or risk form bot signups-->
                <div style="position: absolute; left: -5000px;" aria-hidden="true"><input type="text" name="b_92fa02f66d82bdb55a4381221_9d5ccee144" tabindex="-1" value=""></div>
             </div>
          </div>
       </form>
    </div><!--End mc_embed_signup-->
    <br><br>
