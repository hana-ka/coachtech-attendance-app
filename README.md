# coachtech勤怠管理アプリ

## 概要
勤怠管理を行うためのアプリケーションです。

一般ユーザーは出勤・退勤・休憩の打刻や勤怠修正申請を行うことができ、管理者は全スタッフの勤怠管理および修正申請の承認を行うことができます。

メール認証機能を実装しており、認証済みユーザーのみがサービスを利用できます。

---

## 機能一覧
### 一般ユーザー

* ユーザー登録
* ログイン / ログアウト
* メール認証
* 出勤打刻
* 退勤打刻
* 休憩開始
* 休憩終了
* 勤怠一覧表示
* 勤怠詳細表示
* 勤怠修正申請
* 修正申請一覧表示

### 管理者

* 管理者ログイン
* 当日の勤怠一覧表示
* スタッフ一覧表示
* スタッフ別月次勤怠一覧表示
* 勤怠詳細確認・修正
* 修正申請一覧表示
* 修正申請承認

---

## 環境構築

### Dockerビルド
```bash
git clone git@github.com:hana-ka/coachtech-attendance-app.git

cd coachtech-attendance-app

docker-compose up -d --build
```

### Laravel環境構築
```bash
docker-compose exec php bash
composer install
cp .env.example .env
```

.envファイルを以下のように設定してください
```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass

MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=test@example.com
MAIL_FROM_NAME="${APP_NAME}"

```

```bash
php artisan key:generate
php artisan migrate
php artisan db:seed
```

## 使用技術（実行環境）

* PHP 8.x
* Laravel 8.x
* MySQL 8.0.26
* nginx 1.21.1
* Docker
* Docker Compose
* Git / GitHub

## 開発環境・ツール

* phpMyAdmin
* MailHog

## ER図
![ER図](public/images/er-diagram.png)

## URL

* 開発環境：http://localhost/
* phpMyAdmin：http://localhost:8080/
* MailHog：http://localhost:8025/

## テスト
テストは以下のコマンドで実行できます。
```bash
php artisan test
```
### テスト用データベースについて

テスト実行時に既存データが影響を受けないよう、テスト専用のデータベースを使用しています。

必要に応じて `.env.testing` を作成し、テスト用DBを設定してください。


## ログイン情報

### 管理者

メールアドレス：admin@example.com

パスワード：password

### 一般ユーザー

メールアドレス：user@example.com

パスワード：password


## 補足
* Laravel Fortifyを使用して認証機能を実装しています
* メール認証はMailHogを利用しています
* FormRequestを利用したバリデーションを実装しています
* Featureテストを実装しています