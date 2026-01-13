Laravel開発
====
Laravel開発に関する情報を以下に記載する。

## ■開発関連
---
### ヘッダーコメント
* copyright の項目は必須とする。
```
/**
 * @copyright © 2025 CodeSpaces
 */
```

※但し、テストコードは別。（テストコードは納品しない想定）
```
/**
 * @copyright © 2025 CodeSpaces
 */
```

### 命名規則
命名規則については、以下とする。  
参考サイト：  
- [Laravelベストプラクティス](https://github.com/alexeymezenin/laravel-best-practices/blob/master/japanese.md#Laravel%E3%81%AE%E5%91%BD%E5%90%8D%E8%A6%8F%E5%89%87%E3%81%AB%E5%BE%93%E3%81%86)
- [【laravel】laravelの命名規則 -- Qiita](https://qiita.com/gone0021/items/e248c8b0ed3a9e6dbdee)

| 種類 | 規則 | Good | Bad | 備考 |
|----|----|----|----|----|
| コントローラー名 | 単数形、<br>アッパーキャメルケース | ArticleController | ~~ArticlesController~~ |  |
| ルート名 | 複数形、<br>スネークケース | articles/1 | ~~article/1~~ |  |
| 名前付きルート名 | スネークケースとドット表記 | users.show_active | ~~users.show-active, <br>show-active-users~~ |  |
| モデル名 | 単数形、<br>アッパーキャメルケース | User | ~~Users~~ |  |
| テーブル名 | 複数形、<br>スネークケース | article_comments | ~~article_comment, <br>articleComments~~ | なるべく省略形は使用しない。 |
| テーブルカラム名 | スネークケース | meta_title | ~~MetaTitle, <br> article_meta_title~~ | なるべく省略形は使用しない。 |
| メソッド名 | ローワーキャメルケース |userData, <br>itemList|~~UserData, <br>item_list~~|  |
| views名 | スネークケース |index, <br>users_add|~~Index, <br>usersAdd~~| blade.phpも同様。 |
| 変数名 | スネークケース |$user_data, <br>$item_list|~~$userData<br>$item-list~~| PSRで変数名には指定なし。 |

## ■LibreOffice
---
PDF変換の際に使用。

### インストール
インストールコマンド：
```sh
$ sudo yum install libreoffice libreoffice-langpack-ja
```
※「libreoffice-langpack-ja」は、日本語化パッケージ  

※バージョン（2021/10/21時点）：
```
 libreoffice               1:5.3.6.1-25.el7_9
 libreoffice-langpack-ja   1:5.3.6.1-25.el7_9
```

インストールチェック（ヘルプ表示で）：
```sh
$ /usr/lib64/libreoffice/program/soffice.bin --help
LibreOffice 5.3.6.1 30(Build:1)

Usage: soffice [options] [documents...]
・・・
```


## ■PHPUnit
---
### テスト用DB追加
* テストDB名は、「test_develop」。
* rootのパスワードは、「vagrant」。
* テストユーザーのパスワードも「vagrant」。

```Shell
# mysql -u root -pvagrant -e "CREATE DATABASE test_develop CHARACTER SET UTF8mb4 collate utf8mb4_bin";
# mysql -u root -pvagrant -e "CREATE USER 'test_develop'@'localhost' IDENTIFIED BY 'vagrant';"
# mysql -u root -pvagrant -e "GRANT ALL ON test_develop.* TO 'test_develop'@'localhost';"
```

※コマンド上にパスワード記載しているので、以下Warningが出力されるが特に問題ない。  
```「mysql: [Warning] コマンドラインインターフェイスでパスワードを使用すると、安全でない場合があります。」```

```Shell
[root@localhost ~]# mysql -u root -pvagrant -e "CREATE DATABASE test_develop CHARACTER SET UTF8mb4 collate utf8mb4_bin";
mysql: [Warning] Using a password on the command line interface can be insecure.
```

## ■PHP_CodeSniffer
---

ルートディレクトで以下コマンドを実行すると、コード規約チェックされます。

```Shell
$ composer sniffer
```

成功例：
```Shell
[createadmin@localhost develop]$ composer sniffer
> ./vendor/bin/phpcs --standard=phpcs.xml ./
............................................................  60 / 106 (57%)
..............................................               106 / 106 (100%)


Time: 2.48 secs; Memory: 10MB

[createadmin@localhost develop]$ 
```


以上
