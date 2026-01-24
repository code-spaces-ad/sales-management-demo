販売管理システムのWEBシステム開発
====

## ■ディレクトリ構成
```
project
├── develop/   -- Laravel
├── docker/    -- Docker
└── README.md  -- READMEファイル
```

## ■システム構成
* Webサーバー：nginx 1.25.2  
* プログラム言語：  
  * PHP 8.4  
  * nodejs latest  
* フレームワーク：Laravel 11  
* DB：MySQL 8.4  

## ■本システムの主なポイント
* Laravel(バックエンド) + Blade(フロントエンド)  
* サービス(Service)とリポジトリ(Repository)設計 + ヘルパー活用  
  * 責務分離とテストのしやすい構造  
* トランザクション制御  
  * DB::beginTransaction / commit / rollback で整合性を担保  
* 非同期処理  
  * Laravel Queue / Pusher / Laravel Echo 使用  
* ロギングの使用  
  * ログの出力時にフォーマットする事により、デバッグのしやすさ向上  
* ExcelとPDF出力に対応  
  * PHPSpreadsheet / LibreOffice 使用  
* 通知設定  
  * Slack / Teams に対応  
* APIの使用  
  * 内部 及び 外部 共に使用  
* テストコード作成  
  * Laravel Pint 使用  
* Laravel 6 → 11 へアップグレードを行ったシステム  

## ■開発
* 詳細は、「[README-DEV.md](./develop/README-DEV.md)」 を参照。
* Docker詳細は、[README-docker.md](./develop/README-docker.md)」 を参照。

## ■構築時、Docker側のenvファイルにPJの設定をしてください
* [docker/.env](docker/.env)

```
# Project
ENV_NO=1
COMPOSE_PROJECT_NAME=sales_management_${ENV_NO}

# Ports
PHP_PORT=5173
NGINX_PORT=80
DB_PORT=3306
MAILPIT_PORT1=1025
MAILPIT_PORT2=8025
```

以上
