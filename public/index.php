<!doctype html>
<html lang="pt-br">
<head>
    <link href="https://fonts.googleapis.com/css?family=Nunito+Sans:400,700|Ubuntu:500&display=swap" rel="stylesheet">

    <style>
        * { box-sizing: border-box; }

        h1 {
            opacity: 0.8;
            color: #121E48;
            font-family: 'Ubuntu', sans-serif;
            font-size: 18px;
            font-weight: 500;
            line-height: 21px;
            margin-block-start: 8px;
            margin-block-end: 8px;
        }

        p, span {
            color: #121E48;
            font-family: 'Nunito Sans', sans-serif;
            font-size: 14px;
            line-height: 19px;
            margin-block-start: 30px;
            margin-block-end: 30px;
        }

        .p-title {
            margin-bottom: 8px;
        }

        a {
            color: inherit;
            font-weight: bold;
        }

        .button {
            display: block;
            width: 190px;
            border-radius: 20px;
            background: linear-gradient(135deg, #BC4CF7 0%, #7D71EF 100%);
            color: #FFFFFF;
            font-family: Ubuntu;
            font-size: 12px;
            line-height: 16px;
            text-align: center;
            text-decoration: none;
            padding: 12px 0;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .code {
            width:100%;
            text-align: left;
            padding-left: 30px;
            padding-top: 30px;
            padding-bottom: 30px;
        }

        .division-bar {
            height: 4px;
            width: 100px;
            border-radius: 4px;
            background: linear-gradient(135deg, #BC4CF7 0%, #7D71EF 100%);
        }

        .mt40 {
            margin-top: 40px;
        }

        /* pre {
            background: linear-gradient(135deg, #BC4CF7 0%, #7D71EF 100%);
            color: #fff;
        } */
    </style>
</head>
<body style="margin: 0; background-color: #fff">
    <div style="max-width: 600px; margin: 0 auto;">
        <table
            cellpadding="0"
            cellspacing="0"
            cellpadding="0"
            border="0"
            style="width: 100%; height: 100%; border-spacing: 0;
                border: 0; border-collapse: collapse;"
        >
            <tr>
                <td>
                    <img
                        src="https://images-convenia.s3.amazonaws.com/email/convenia.png"
                        alt="convenia-logo"
                        style="padding: 60px 0 62px 40px;"
                    >
        
                    <h1 style="margin-left: 40px;">
                        <small>
                            Convenia PHP Full Image
                        </small>
                    </h1>
        
                    <div class="division-bar" style="margin-left: 40px;">
                    </div>
                </td>
        
                <td style="text-align: right; line-height: normal;">
                    <img
                        src="https://images-convenia.s3.amazonaws.com/email/background.png"
                        alt="convenia-logo"
                        style="line-height: normal; margin-top: -3px;"
                    >
                </td>
            </tr>
        </table>
        <div style="padding-left: 40px; padding-right: 40px;">
            <p>
                This image was built by the Convenia team in order to get some PHP applications running in the simplest way. This image can be used for production and development.
            </p>

            <!-- <a href="/phpinfo.php" class="button">
                See full documentation
            </a> -->

            <p></p>

            <p class="p-title"><strong>How to use</strong></p>

            <div class="division-bar">
            </div>

            <p>
                All you need to do is creating a docker-compose.yml like this in your project root directory:
            </p>

            <pre class="button code">
version: '3.3'
services:
  app:
    image: convenia/php-full:latest
    container_name: application
    volumes:
      - .:/var/www/app
    ports:
      - 80:80</pre>

            <p>
                The container will try to serve the application inside the "public" directory then if your application does not have a public directory you can <a href="#override-conf">override the nginx config</a> to look at the correct location
            </p>

            <p class="p-title"><strong>Change User and Group</strong></p>

            <div class="division-bar">
            </div>

            <p>
                The application will run with the user "app" by default. This user has the id and group 1000. We can change the id running this commands at our own Dockerfile:
            </p>

            <pre class="button code">
RUN addgroup -S -g 2000 newone && adduser -u 2000 -G newone -D newone && \
    sed -i 's/app/newone/g' /usr/local/etc/php-fpm.d/www.conf && \
    chown -R newone:newone /var/www/app</pre>

            <p>
                This line will create a new group and user with the chosen ID then it will start php-fpm with this user and group.
            </p>

            <p class="p-title"><strong>Security</strong></p>

            <div class="division-bar">
            </div>

            <p>
                The image build runs every week in order to ensure that all software are up to date and we keep the Docker Hub scan to ensure that our image is free of vulnerabilities.
            </p>

            <p>
                <b>
                    IMPORTANT: Make sure to block the 9000 port to avoid exposing the fpm entrypoint. We keep fpm listening only requests from localhost(container) but make sure of block the 9000 port on any firewall. Avoid Docker "network driver host" at all cost!!
                </b>
            </p>

            <p id="override-conf"><strong>Change Nginx Configuration</strong></p>

            <div class="division-bar">
            </div>

            <p>The nginx configuration can be found <a href="https://github.com/convenia/php-full-8.1-image/blob/main/docker/nginx/default.conf">here</a>. Usually we need to override this configuration for some reason.</p>
            <p>We can override nginx configuration at build time by coping a new onfiguration over the default one</p>
            <pre class="button code">
FROM convenia/php-full:latest

COPY ./local-path-to-config/new-default.conf /etc/nginx/http.d/default.conf</pre>

            <p>Or we can override the default configuration at mount time:</p>
            <pre class="button code">
version: '3.3'
services:
  app:
    image: convenia/php-full
    container_name: application
    volumes:
      - ./local-path-to-config/new-default.conf:/etc/nginx/http.d/default.conf
    ports:
      - 80:80</pre>
            
            <p class="p-title"><strong>Bugs, and suggestion</strong></p>

            <div class="division-bar">
            </div>

            <p>
                For any suggestion or bug try to open an issue on github or send a tweet to us
            </p>

            <a href="https://github.com/convenia/php-full-8.1-image" class="button"  style="display: inline-block;">
                Github
            </a>

            <a target="_BLANK" href="https://twitter.com/convenia" class="button" style="display: inline-block;">
                Twitter
            </a>

            <p></p>
            
            </div>
            <hr
                style="width: 540px; height: 1px; background-color: rgba(18,30,72,0.1);
                    border: none; margin: 45px auto 0; padding: 0 20px;"
            >
            
            <img
                width="100px"
                src="https://images-convenia.s3.amazonaws.com/email/logo.png"
                alt="convenia-logo-small"
                style="display: block; margin: 20px auto;"
            >            
    </div>
</body>
</html>