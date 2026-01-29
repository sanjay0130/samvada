{strip}
<style>
    body {
        background: linear-gradient(135deg, #4facfe 0%, #4facfe 100%);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
    }

    .login-container {
        background: #fff;
        width: 380px;
        border-radius: 12px;
        padding: 40px 30px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        text-align: center;
        animation: fadeIn 0.8s ease;
    }

    .login-container img.user-logo {
        height: 80px;
        margin-bottom: 20px;
    }

    .login-title {
        font-size: 22px;
        font-weight: 600;
        margin-bottom: 25px;
        color: #333;
    }

    .form-group {
        margin-bottom: 20px;
        text-align: left;
    }

    .form-group input, .form-group select {
        width: 100%;
        padding: 12px 14px;
        font-size: 15px;
        border-radius: 6px;
        border: 1px solid #ddd;
        outline: none;
        transition: 0.3s;
    }

    .form-group input:focus, .form-group select:focus {
        border-color: #35aa47;
        box-shadow: 0 0 4px rgba(53, 170, 71, 0.4);
    }

    .btn {
        display: block;
        width: 100%;
        background: #35aa47;
        color: #fff;
        border: none;
        padding: 12px;
        border-radius: 6px;
        font-size: 16px;
        cursor: pointer;
        transition: 0.3s;
    }

    .btn:hover {
        background: #2d8e3d;
    }

    .forgotPasswordLink {
        display: inline-block;
        margin-top: 15px;
        font-size: 14px;
        color: #007bff;
        text-decoration: none;
        transition: 0.3s;
    }

    .forgotPasswordLink:hover {
        text-decoration: underline;
    }

    .failureMessage {
        color: #e74c3c;
        font-size: 14px;
        margin-bottom: 10px;
    }

    .successMessage {
        color: #27ae60;
        font-size: 14px;
        margin-bottom: 10px;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<div class="login-container">
    <img class="img-responsive user-logo" src="{$COMPANY_LOGO->get('imagepath')}" alt="{$COMPANY_LOGO->get('alt')}"/>
    <div class="login-title">Welcome Back</div>

    <div>
        <span class="{if !$ERROR}hide{/if} failureMessage" id="validationMessage">{$MESSAGE}</span>
        <span class="{if !$MAIL_STATUS}hide{/if} successMessage">{$MESSAGE}</span>
    </div>

    <div id="loginFormDiv">
        <form method="POST" action="index.php">
            <input type="hidden" name="module" value="Users"/>
            <input type="hidden" name="action" value="Login"/>

            <div class="form-group">
                <input id="username" type="text" name="username" placeholder="Username">
            </div>
            <div class="form-group">
                <input id="password" type="password" name="password" placeholder="Password">
            </div>

            {assign var="CUSTOM_SKINS" value=Vtiger_Theme::getAllSkins()}
            {if !empty($CUSTOM_SKINS)}
            <div class="form-group">
                <select id="skin" name="skin">
                    <option value="">Default Skin</option>
                    {foreach item=CUSTOM_SKIN from=$CUSTOM_SKINS}
                    <option value="{$CUSTOM_SKIN}">{$CUSTOM_SKIN}</option>
                    {/foreach}
                </select>
            </div>
            {/if}

            <button type="submit" class="btn">Sign in</button>
            <a class="forgotPasswordLink">Forgot password?</a>
        </form>
    </div>

    <div id="forgotPasswordDiv" class="hide">
        <form action="forgotPassword.php" method="POST">
            <div class="form-group">
                <input id="fusername" type="text" name="username" placeholder="Username">
            </div>
            <div class="form-group">
                <input id="email" type="email" name="emailId" placeholder="Email">
            </div>
            <button type="submit" class="btn">Submit</button>
            <a class="forgotPasswordLink">Back</a>
        </form>
    </div>
</div>

<script>
    jQuery(document).ready(function () {
        var validationMessage = jQuery('#validationMessage');
        var forgotPasswordDiv = jQuery('#forgotPasswordDiv');
        var loginFormDiv = jQuery('#loginFormDiv');

        loginFormDiv.find('#password').focus();

        // toggle login/forgot forms
        jQuery('.forgotPasswordLink').click(function () {
            loginFormDiv.toggleClass('hide');
            forgotPasswordDiv.toggleClass('hide');
            validationMessage.addClass('hide');
        });

        // validation
        loginFormDiv.find('button').on('click', function () {
            var username = loginFormDiv.find('#username').val();
            var password = jQuery('#password').val();
            if (username === '' || password === '') {
                validationMessage.removeClass('hide').text('Please enter valid credentials');
                return false;
            }
        });
    });
</script>
{/strip}
