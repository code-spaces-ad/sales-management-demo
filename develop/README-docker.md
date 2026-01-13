Laravel開発(Docker)
====
Laravel開発(Docker環境構築)に関する情報を以下に記載する。

## ■事前準備

* インストール
  * docker for desktop

## ■.env設定

* `APP_URL=http://localhost`　※localhostにする
* `DB_HOST=db` ※コンテナ名にする

## ■初期構築

* `cd docker`
* `docker compose up -d`
* Windowsのみ：`exec winpty bash`
* `docker compose exec php bash`
* `find /var/www/storage -type d -print0 | xargs -0 chmod 707`
* `find /var/www/public -type d -print0 | xargs -0 chmod 707`
* `cp .env.docker .env`
* `composer install`
* `php artisan migrate:refresh --seed`
* `npm install`
* `npm run dev`

## ■起動（初期構築後はこちらでOK）

* `docker compose up -d`

## ■docker閉じる

* `docker compose down`

## ■マスタインポート
* 取込用csvファイル置き場：develop/storage/app/import
* truncate を任意指定することで truncate処理実施

| No | 機能       | コマンド                                              | オプション                                                                               | 備考                          |
|----|----------|---------------------------------------------------|---|-----------------------------|
| 1  | 得意先マスタ取込 | import:master-customers master-customers.csv true | * 第１引数：ファイル名(デフォルト=master-customers.csv/省略可)<br/>* 第２引数：Truncate可否(デフォルト=false/省略可) | * 同時に得意先敬称マスタも登録            |
| 2  | 仕入先マスタ取込 | import:master-suppliers master-suppliers.csv true | * 第１引数：ファイル名(デフォルト=master-suppliers.csv/省略可)<br/>* 第２引数：Truncate可否(デフォルト=false/省略可) | * 同時に仕入先敬称マスタも登録            |
| 3  | 商品マスタ取込  | import:master-products master-products.csv true  | * 第１引数：ファイル名(デフォルト=master-products.csv/省略可)<br/>* 第２引数：Truncate可否(デフォルト=false/省略可) | * 得意先マスタ必要<br>* 同時に単位マスタも登録 |

## ■締処理関連

### 本対応で使用した主な技術は以下

* Laravel Queue
* Pusher
* Laravel Echo

### キュー起動

* 締処理を行う際には以下コマンド実行
    * `php artisan queue:work`

### 設定

* Pusherの設定は、以下.env

[./.env](./.env)

```dotenv
BROADCAST_DRIVER=pusher
QUEUE_CONNECTION=database

PUSHER_APP_ID=1951974
PUSHER_APP_KEY=f605e24d3b51f9ce5f21
PUSHER_APP_SECRET=330f663f9507a653545c
PUSHER_APP_CLUSTER=ap3
```

* Laravel Echoの設定は、以下js

[./resources/js/bootstrap.js](./resources/js/bootstrap.js)
```javascript
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: process.env.MIX_PUSHER_APP_KEY,
    cluster: process.env.MIX_PUSHER_APP_CLUSTER,
    encrypted: true,
    authEndpoint: '/broadcasting/auth',
    auth: {
        withCredentials: true,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        }
    }
});
```

* 対象チャンネルの認証

[./routes/channels.php](./routes/channels.php)
```php
Broadcast::channel('charge_closing_channel.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
```

* その他、コードは [./app/Events/](./app/Events/) や [./app/Jobs/](./app/Jobs/) 等に記載

## ■その他

* php-stormのターミナルをgitbash設定推奨
* 上記設定があれば、そのままターミナル上で初期設定等が可能になる
* php-storm上のDB接続が直接可能

以上
