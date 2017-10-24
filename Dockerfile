FROM hhvm/hhvm:3.22.1

LABEL maintainer="paulo.cuchi@gmail.com"

COPY src /usr/share/quack

RUN echo "hhvm /usr/share/quack/Main.php \$@" > /bin/quack \
    && chmod +x /bin/quack

CMD ["hhvm", "/usr/share/quack/Main.php"]

