=== Clinic Contact ===
Contributors: hatchdogs
Tags: shortcode, contact form, smtp
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

問い合わせフォーム。ショートコードで設置でき、送信は SMTP 経由で行います。

== Description ==

お名前・メールアドレス・電話番号（任意）・ご相談内容を受け取り、管理者宛にメールを送ります。

* ショートコード `[clinic_contact]` で任意の場所に設置
* 送信は SMTP 認証経由。PHP の `mail()` による直送は使いません
* テーマを問わず動作します

= 設計について =

* **SMTP が未設定なら送信しない**：`mail()` に落ちると SPF / DKIM が付かず、届いても迷惑メールに入ります。
  黙って劣化させるより、設定漏れとして失敗させたほうが気づけます。定数が揃っていない場合は
  `error_log()` に理由を残したうえで送信を拒否します。
* **SMTP AUTH のログイン ID と差出人アドレスは別物**：メールサーバーによってはログイン ID が
  ローカル部だけ（例 `info`）で、差出人は SPF / DKIM を通すために完全なアドレスが必要です。
  そのため `CLINIC_SMTP_USER` と `CLINIC_SMTP_FROM` を分けています。
* **スパム対策は3段**：nonce（CSRF）、ハニーポット（`cc_website` に入力があれば破棄）、
  時間トラップ（フォーム描画から送信までが 3 秒未満なら破棄）。CAPTCHA は使っていません。
* **送信先ページを固定しない**：`action` を空にして現在の URL へ POST します。
  `get_permalink()` はフロントページが投稿一覧のときループ内の投稿 URL を返すため、
  それを使うと別のページへ飛びます。

== Installation ==

1. `clinic-contact` を `wp-content/plugins/` に置く
2. `wp-config.php` に SMTP の定数を定義する（下記）
3. プラグインを有効化する
4. 固定ページや投稿、テンプレートに `[clinic_contact]` を書く

必要な定数:

`define( 'CLINIC_SMTP_HOST', 'mail.example.net' );`
`define( 'CLINIC_SMTP_PORT', 465 );`
`define( 'CLINIC_SMTP_USER', 'ログインID' );`
`define( 'CLINIC_SMTP_FROM', 'info@example.net' );`
`define( 'CLINIC_SMTP_PASS', '...' );`

ポートが 465 なら SMTPS（暗黙 TLS）、それ以外は STARTTLS で接続します。

== Frequently Asked Questions ==

= 送信されません =

`wp-config.php` の SMTP 定数が揃っているか確認してください。ひとつでも欠けていると、
このプラグインは `mail()` に落とさず送信を拒否します。理由は PHP のエラーログに出ます。

= 届くけれど迷惑メールに入ります =

送信元ドメインの SPF / DKIM / DMARC を確認してください。`CLINIC_SMTP_FROM` は
SPF に含まれるドメインのアドレスである必要があります。

== Changelog ==

= 1.0.0 =
* 初回リリース。
