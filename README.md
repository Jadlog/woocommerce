# Extensão de frete Jadlog - WooCommerce

Este plugin para [WooCommerce](https://woocommerce.com) disponibiliza opções de frete Jadlog no carrinho de compras e implementa integração com a API de solicitações de coleta.


## Requisitos

* PHP 7.3
* MySQL 5.6
* [Wordpress](https://wordpress.org) 5.2 - 5.6
* [WooCommerce](https://woocommerce.com) 3.9.3 - 4.9.1
* Plugin [Brazilian Market on WooCommerce](https://wordpress.org/plugins/woocommerce-extra-checkout-fields-for-brazil/) 3.7


## Instruções de instalação

### Preparação
* Instalar o *Wordpress*;
* Instalar e ativar o plugin *WooCommerce*;
* Opcional: instalar e ativar o plugin *WooCommerce-Admin*;
* Instalar e ativar o plugin *Brazilian Market on WooCommerce*;

### Instalação deste plugin
* Na página de *[releases](https://github.com/Jadlog/woocommerce/releases)* fazer download deste plugin no formato .zip 
* No painel de administração do Wordpress, faça upload do arquivo .zip em *Plugins* -> *Adicionar novo* -> *Enviar plugin*;
* Configure o plugin em *WooCommerce* -> *Configurações* -> *Aba Jadlog* (será necessário fornecer dados do seu contrato com a Jadlog &ndash; entre em contato com o Depto. Comercial da Jadlog caso ainda não o tenha feito).


#### Manual de instalação e uso
* [Manual de Instalação e Utilização](doc/MANUAL.md)


## Ambiente de desenvolvimento

O ambiente de desenvolvimento utiliza imagens [Docker](https://www.docker.com) e ferramenta [Docker Compose](https://docs.docker.com/compose/).

Após clonar este repositório, rode `docker-compose up -d wordpress-dev` para subir dois contêineres:

- Wordpress (PHP + Apache + Wordpress)
- MySQL

Acesse então http://localhost:8080/wp-admin para configurar o Wordpress e ativar os plugins *WooCommerce*, *Brazilian Market on WooCommerce* e *WooCommerce Jadlog* (já devem estar instalados).


## Testes

Instale as bibliotecas:

```bash
$ docker-compose run --rm composer install
```

Para rodar todos os testes:

```bash
$ docker-compose run --rm codecept run
```

Para rodar um teste específico:

```bash
$ docker-compose run --rm codecept tests/wpunit/WooCommerce/Jadlog/Classes/EmbarcadorServiceTest.php
```

Para rodar com uma versão específica do Wordpress e/ou WooCommerce, use as
variáveis de ambiente `$WORDPRESS_VERSION` e `$WOOCOMMERCE_VERSION`,
respectivamente:

```bash
$ docker-compose rm -s wordpress-test #excluir o container para reconstruir o volume com a versão correta
$ WOOCOMMERCE_VERSION=4.8.0 docker-compose run --rm codecept
```

Os testes de aceitação rodam com selenium/chrome/webdriver no modo _headless_.
Para rodar no modo visual, abra com VNC o endereço `vnc://localhost:<porta vnc do container do chrome>` e rode:

```bash
$ docker-compose run --rm -e HEADLESS= codecept run acceptance
```

### Upgrade do banco de testes

Quando há atualizações do Wordpress ou WooCommerce, é exigido que o banco de dados também seja atualizado. Assim, é preciso atualizar o arquivo dump `woocommerce-jadlog/tests/_data/wordpress_test.sql`

1. Entrar num shell do conteiner `wordpress-test`:
```bash
    $ docker-compose exec wordpress-test bash
```
2. Instalar o [wp-cli](https://wp-cli.org/#installing):
```bash
    $ curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
    $ chmod +x wp-cli.phar
    $ mv wp-cli.phar /usr/local/bin/wp
```
3. Atualizar o [esquema do Wordpress](https://developer.wordpress.org/cli/commands/core/update-db/):
```bash
    $ wp core update-db
```
4. Atualizar o [esquema do WooCommerce](https://github.com/woocommerce/woocommerce/wiki/Upgrading-the-database-using-WP-CLI):
```bash
    $ wp wc update
```
5. Fazer um dump do banco `wordpress_test` e atualizar o arquivo `woocommerce-jadlog/tests/_data/wordpress_test.sql`

## Error monitoring

Este plugin pode se integrar ao serviço de monitoramento de erros [Bugsnag](https://www.bugsnag.com).

1. Crie uma conta no Bugsnag;
1. No painel de administração do Wordpress, instale o plugin [WordPress Error Monitoring by Bugsnag](https://br.wordpress.org/plugins/bugsnag/);
1. Configure o plugin, inserindo a *Bugsnag API Key* e os tipos de erros a reportar: *Crashes, errors, warnings & info messages*;
1. Agora todas os erros, avisos e mensagens do PHP e logs de erro gerados por este plugin serão enviados ao painel do Bugsnag.

## Changelog
- v0.5.1
  - Alteração de URL's externas da API Jadlog para HTTPS
- v0.5.0
  - Adição de modalidade econômico
- v0.4.0
  - Suporte a zonas de entrega. *Configuração obrigatória*.
- v0.3.0
  - Suporta Wordpress 5.6 e WooCommerce 4.9
- v0.2.1
  - Correção de bug nos pedidos de coleta.
- v0.2.0
  - Modalidade Package.
- v0.1.4
  - Correção na integração com *Brazilian Market on WooCommerce*.
- v0.1.3
  - Correções de bugs.
- v0.1.2
  - WooCommerce 4.1.1;
  - Integração com [Bugsnag](https://www.bugsnag.com) para monitoramento de erros.
- v0.1.1
  - Utilização da mesma chave do Embarcador para consulta de PUDOs.
- v0.1.0
  - Lançamento (versão beta).

## Licença

[GNU General Public License Version 3](https://www.gnu.org/licenses/gpl-3.0.html)

Texto completo em [LICENSE.txt](woocommerce-jadlog/LICENSE.txt).

## Desenvolvimento

[Jadlog](https://www.jadlog.com.br) - *Uma empresa DPDgroup*

