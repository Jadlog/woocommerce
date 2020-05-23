# Extensão de frete Jadlog - WooCommerce

Este plugin para [WooCommerce](https://woocommerce.com) disponibiliza opções de frete Jadlog no carrinho de compras e implementa integração com a API de solicitações de coleta.


## Requisitos

* PHP 7.3
* MySQL 5.6
* [Wordpress](https://wordpress.org) 5.2 - 5.4
* [WooCommerce](https://woocommerce.com) 3.8.1 - 4.1.1
* Plugin [Brazilian Market on WooCommerce](https://wordpress.org/plugins/woocommerce-extra-checkout-fields-for-brazil/) 3.7


## Instruções de instalação

### Preparação
* Instalar o *Wordpress*;
* Instalar e ativar o plugin *WooCommerce*;
* Opcional: instalar e ativar o plugin *WooCommerce-Admin*;
* Instalar e ativar o plugin *Brazilian Market on WooCommerce*;

### Instalação deste plugin
* Fazer [download](downloads/) deste plugin no formato .zip 
* No painel de administração do Wordpress, faça upload do arquivo .zip em *Plugins* -> *Adicionar novo* -> *Enviar plugin*;
* Configure o plugin em *WooCommerce* -> *Configurações* -> *Aba Jadlog* (será necessário fornecer dados do seu contrato com a Jadlog &ndash; entre em contato com o Depto. Comercial da Jadlog caso ainda não o tenha feito).


#### Manual de instalação e uso
* [Manual de Instalação e Utilização](doc/MANUAL.md)


## Ambiente para desenvolvimento

O ambiente de desenvolvimento utiliza imagens [Docker](https://www.docker.com) e ferramenta [Docker Compose](https://docs.docker.com/compose/).

Após clonar este repositório, rode `docker-compose up -d` para subir três contêineres:

- Wordpress (PHP + Apache + Wordpress)
- MySQL
- PHPUnit

Acesse então http://localhost:8080/wp-admin para configurar o Wordpress e ativar os plugins *WooCommerce*, *Brazilian Market on WooCommerce* e *WooCommerce Jadlog* (já devem estar instalados).

Para rodar os testes automatizados:

`docker-compose run --rm test phpunit`

## Error monitoring

Este plugin pode se integrar ao serviço de monitoramento de erros [Bugsnag](https://www.bugsnag.com).

1. Crie uma conta no Bugsnag;
1. No painel de administração do Wordpress, instale o plugin [WordPress Error Monitoring by Bugsnag](https://br.wordpress.org/plugins/bugsnag/);
1. Configure o plugin, inserindo a *Bugsnag API Key*;
1. Agora todas as exceções do PHP e logs de erro gerados por este plugin serão enviados ao painel do Bugsnag.

## Changelog

- v0.1.0
  - Lançamento (versão beta).
- v0.1.1
  - Utilização da mesma chave do Embarcador para consulta de PUDOs.
- v0.1.2
  - WooCommerce 4.1.1;
  - Integração com [Bugsnag](https://www.bugsnag.com) para monitoramento de erros.
- v0.1.3
  - Correções de bugs.

## Licença

[GNU General Public License Version 3](https://www.gnu.org/licenses/gpl-3.0.html)

Texto completo em [LICENSE.txt](woocommerce-jadlog/LICENSE.txt).

## Desenvolvimento

[Jadlog](http://www.jadlog.com.br) - *Uma empresa DPDgroup*

