## Convenia PHP Full Image

This image was built by the Convenia team in order to get some PHP applications running in the simplest way. This image can be used for production and development.

### How to use

All you need to do is creating a docker-compose.yml like this in your project root directory:

```yml
version: '3.3'
services:
  app:
    image: convenia/php-full:latest
    container_name: application
    volumes:
      - .:/var/www/app
    ports:
      - 80:80
```

The container will try to serve the application inside the "public" directory then if your application does not have a public directory you can [override the nginx config](#change-nginx-configuration) to look at the correct location

### Change User and Group

The application will run with the user "app" by default. This user has the id and group 1000. We can change the id running this commands at our own Dockerfile:

```Dockerfile
RUN addgroup -S -g 2000 newone && adduser -u 2000 -G newone -D newone && \
    sed -i 's/app/newone/g' /usr/local/etc/php-fpm.d/www.conf && \
    chown -R newone:newone /var/www/app
```

This line will create a new group and user with the chosen ID then it will start php-fpm with this user and group.

### Security

The image build runs every week in order to ensure that all software are up to date and we keep the Docker Hub scan to ensure that our image is free of vulnerabilities.

**IMPORTANT: Make sure to block the 9000 port to avoid exposing the fpm entrypoint. We keep fpm listening only requests from localhost(container) but make sure of block the 9000 port on any firewall. Avoid Docker "network driver host" at all cost!!**

### Change Nginx Configuration

The nginx configuration can be found [here](https://github.com/convenia/php-full-8.1-image/blob/main/docker/nginx/default.conf). Usually we need to override this configuration for some reason.

We can override nginx configuration at build time by coping a new onfiguration over the default one

```Dockerfile
FROM convenia/php-full:latest

COPY ./local-path-to-config/new-default.conf /etc/nginx/http.d/default.conf</pre>
```

Or we can override the default configuration at mount time:

```yml
version: '3.3'
services:
  app:
    image: convenia/php-full
    container_name: application
    volumes:
      - ./local-path-to-config/new-default.conf:/etc/nginx/http.d/default.conf
    ports:
      - 80:80
```
### How to cron
The image contains the cron binary then lets take a look how easy would be to spawn the [Laravel Scheduler](https://laravel.com/docs/10.x/scheduling) for example:

1. First step would be add the `crontab` file at your project with this content:

```
* * * * * php /var/www/app/artisan schedule:run
```

2. Second step would be place the crontab file on the default cron directory:

```Dockerfile
FROM convenia/php-full:8.3

ADD . /var/www/app

COPY crontab /etc/crontabs/root
```

3. Build the image

```
docker build -t easycron .
```

4. Now we just need to execute the cron command:

```
docker run --rm easycron crond -l 2 -f
```

### Changelog
#### PHP version 8.3
- We removed Imagick extension. If you wish to use Imagick extension add to your Dockerfile. 


For any suggestion or bug try to [open an issue on github](https://github.com/convenia/php-full-8.1-image) or [send a tweet](https://twitter.com/convenia) to us
