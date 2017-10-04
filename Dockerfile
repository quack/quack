
FROM php:7.1-alpine

RUN apk add tini --update

COPY src /usr/share/quack

RUN echo "php /usr/share/quack/Quack.php \$@" > /bin/quack \
    && chmod +x /bin/quack

CMD ["tini", "--", "php", "/usr/share/quack/repl/QuackRepl.php"]

