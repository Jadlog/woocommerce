FROM debian:10

ARG WORDPRESS_VERSION

RUN apt-get update && \
    apt-get install -y php7.3-cli php7.3-mbstring php7.3-xml php7.3-mysql phpunit \
                       curl default-mysql-client git unzip subversion
RUN curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar && \
    chmod +x wp-cli.phar && \
    mv wp-cli.phar /usr/local/bin/wp
RUN cd / && \
    curl -O https://wordpress.org/wordpress-${WORDPRESS_VERSION}.tar.gz && \
    tar xzf wordpress-${WORDPRESS_VERSION}.tar.gz && \
    rm -f wordpress-${WORDPRESS_VERSION}.tar.gz

VOLUME /wordpress/wp-content/plugins/woocommerce
VOLUME /wordpress/wp-content/plugins/woocommerce-extra-checkout-fields-for-brazil
VOLUME /wordpress/wp-content/plugins/woocommerce-jadlog
VOLUME /wordpress-tests-lib

COPY install-wp-tests.sh /
RUN  chmod +x /install-wp-tests.sh

WORKDIR /wordpress/wp-content/plugins/woocommerce-jadlog

CMD /install-wp-tests.sh

