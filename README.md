## Convenia PHP Full Image

This image was built by the Convenia team in order to get some PHP applications running in the simplest way. This image can be used for production and development.

### How to use

All you need to do is creating a docker-compose.yml like this in your project root directory:

```yml
version: '3.3'
services:
  app:
    image: testing
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

The nginx configuration can be found [here](#github-link). Usually we need to override this configuration for some reason.

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

### Bugs, and suggestion

For any suggestion or bug try to [open an issue on github]() or [send a tweet](https://twitter.com/convenia) to us