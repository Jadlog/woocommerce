FROM debian:10

RUN apt-get update && \
    apt-get install -y php7.3-cli php7.3-mbstring php7.3-xml php7.3-mysql phpunit \
                       curl default-mysql-client git unzip subversion && \
    curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar && \
    chmod +x wp-cli.phar && mv wp-cli.phar /usr/local/bin/wp

VOLUME /wordpress
VOLUME /wordpress/wp-content/plugins/woocommerce-jadlog
VOLUME /wordpress-tests-lib

COPY install-wp-tests.sh /
RUN  chmod +x /install-wp-tests.sh

WORKDIR /wordpress/wp-content/plugins/woocommerce-jadlog

CMD /install-wp-tests.sh

