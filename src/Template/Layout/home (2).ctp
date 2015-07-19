<!DOCTYPE html>
<html>
    <head>
        <?php echo $this->Html->charset() ?>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
        <title>DevInfo Database Administrative Tool</title>

        <?php echo $this->Html->meta('icon') ?>
        <?php echo   $this->Html->css(['reset','style','styleguide','responsive','font-awesome.min']) ?>
    </head>
    <body>

        <header class="main-header darkblue">
            <div class="container">
                <div class="logo"><a href="index.html"><img src="img/logo.png" alt="Database Administrative Tool"></a></div>
                <div class="header-menu">
                    <ul>
                        <li><a rel="register" class="popup" href="javascript:void(0);"> <i class="fa fa-lock"></i> Log in</a></li>
                    </ul>
                </div>
            </div>
        </header>

        <section class="main">
            <div class="scroll">
                <!--video part starts Here-->
                <section class="content">
                    <div class="container">
                        <div class="video-info">
                            <h1>DFA - DevInfo Database Administrative Tool</h1>
                            <p>Lorem Ipsum. Proin gravida nibh vel velit auctor aliquet. Aenean sollicitudin, lorem quis bibendum auctor, nisi elit consequat ipsum, nec sagittis sem nibh id elit. Duis sed odio sit amet nibh vulputate cursus a sit amet mauris. Morbi accumsan ipsum velit. Nam nec tellus a odio tincidunt auctor.nec sagittis sem nibh id elit. Duis sed odio sit amet nibh vulputate cursus a sit amet mauris. Morbi accumsan ipsum velit. Nam nec tellus a odio tincidunt auctor.</p>
                            <a href="#database" class="btn btn-green btn-lg"><i class="fa fa-gear"></i> Start Database Administration </a>
                        </div>
                    </div>
                    <div class="video">
                        <video width="100%" height="auto" autoplay id="bgVideo" poster="img/vid-home-fallback.png" loop>
                            <source type="video/mp4" src="img/AISPL_new_video.mp4"></source>
                            <source type="video/webm" src="img/AISPL_new_video.webm"></source>
                            <img width="100%" height="auto" alt="" src="img/vid-home-fallback.png"> </video>
                    </div>
                </section>
                <!--video part end Here-->
                <!--Section content starts Here-->
                <section class="sections-home">
                    <div class="container">
                        <div class="desc">
                            <h2>DevInfo Database Template</h2>
                            <p>This is Photoshop's version  of Lorem Ipsum. Proin gravida nibh vel velit auctor aliquet. Aenean sollicitudin, lorem quis bibendum auctor, nisi elit consequat ipsum, nec sagittis sem nibh id elit. Duis sed odio sit amet nibh vulputate cursus a sit amet mauris. Morbi accumsan ipsum velit. Nam nec tellus a odio tincidunt auctor a ornare odio. Sed non  mauris vitae erat consequat auctor eu in elit.</p>
                        </div>
                        <div class="desc">
                            <h2>Data Values and Validations</h2>
                            <p>This is Photoshop's version  of Lorem Ipsum. Proin gravida nibh vel velit auctor aliquet. Aenean sollicitudin, lorem quis bibendum auctor, nisi elit consequat ipsum, nec sagittis sem nibh id elit. Duis sed odio sit amet nibh vulputate cursus a sit amet mauris. Morbi accumsan ipsum velit. Nam nec tellus a odio tincidunt auctor a ornare odio. Sed non  mauris vitae erat consequat auctor eu in elit.</p>
                        </div>
                        <div class="desc third">
                            <h2>User Management</h2>
                            <p>This is Photoshop's version  of Lorem Ipsum. Proin gravida nibh vel velit auctor aliquet. Aenean sollicitudin, lorem quis bibendum auctor, nisi elit consequat ipsum, nec sagittis sem nibh id elit. Duis sed odio sit amet nibh vulputate cursus a sit amet mauris. Morbi accumsan ipsum velit. Nam nec tellus a odio tincidunt auctor a ornare odio. Sed non  mauris vitae erat consequat auctor eu in elit.</p>
                        </div>
                    </div>
                </section>
                <section class="sections-home btnbox">
                    <a href="#database" class="btn btn-green btn-lg"><i class="fa fa-gear"></i> Start Database Administration </a>
                </section>
                <!--Section content end Here-->
            </div>
        </section>
        <!--Section ends Here -->

        <!--popup box start here -->
        <div id="register" class="popupbox register">
            <form accept-charset="utf-8" method="post" id="UserLoginForm" data-bind="submit: Login" action=""><div style="display:none;"><input type="hidden" value="POST" name="_method"></div>
                <div class="close" id="close"><a href="javascript:void(0);"><i class="fa fa-close"></i></a></div>
                <div class="poptxt-tp">
                    <figure>
                        <img alt="Login to the DFA" src="img/logo-login.png">
                    </figure>
                    <div class="lg-tp-text">
                        <h3>Sign in To Start Database Administration </h3>
                        <h5>The best platform for Database Administration</h5>
                    </div>

                </div>
                <div class="form-part-main">
                    <div class="right-pop">
                        <div class="scl-btn register-psac">
                            <a class="registe-btn" href="javascript:void(0);">Forgot Password</a>
                        </div>
                    </div>
                    <div class="log-left">
                        <div class="form-group">
                            <div class="input email">
                                <label for="UserEmail">Email</label>
                                <input type="email" id="UserEmail" placeholder="Enter Your Email">
                            </div>
                            <div class="loginerror">
                                <span style="display: none;">*Email is required.</span>
                                <span style="display: none;">*Email Address is invalid.</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="input password">
                                <label for="UserPwd">Password</label>
                                <input type="password" id="UserPwd" placeholder="Enter Your Password">
                            </div>
                            <div class="loginerror">
                                <span style="display: none;">*Password is required.</span>
                            </div>
                        </div>
                        <div class="form-action">
                            <div class="form-remember">
                                <label><input type="checkbox" data-bind="checked: RememberMe">Remember Me</label>
                            </div>
                            <button type="submit">Submit</button>
                        </div>

                    </div>
                </div>

            </form>
        </div>
        <!--popup box Ends here -->
        <div class="overlay dark-opacity" id="fade"></div>

        <!--footer starts Here-->
        <footer class="footer darkblue">
            <div class="container">
                <div class="ft-nav">
                    <ul>
                        <li><a href="javascript:void();">About Us</a></li>
                        <li><a href="javascript:void();">Contact Us</a></li>
                        <li><a href="javascript:void();">FAQ</a></li>
                    </ul>
                </div>
            </div>
        </footer>
    </body>
</html>
