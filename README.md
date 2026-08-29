# さくら整骨院 — WordPress テーマ＋自作プラグイン

交通事故・自賠責に対応する整骨院を想定して制作した、**架空クライアント向けのサンプル作品**です。
実在の院ではありません。

デモ: https://demo-clinic.hatchdogs.net/

既製テーマやページビルダーは使わず、テーマと機能プラグインを PHP で書いています。

## 構成

```
themes/sakura-clinic/            オリジナルテーマ（クラシックテーマ）
plugins/clinic-fee-simulator/    料金シミュレーター
plugins/clinic-contact/          問い合わせフォーム
```

このリポジトリは WordPress の `wp-content/` を作業ツリーにしています。
`.gitignore` で既定をすべて無視し、上の3つだけを追跡しています。
WordPress 本体・`uploads/`・`languages/` は含みません。

## themes/sakura-clinic

| ファイル | 役割 |
|---|---|
| `functions.php` | テーマ設定、カスタマイザー、head の掃除 |
| `front-page.php` | トップページ（8セクション） |
| `header.php` / `footer.php` / `index.php` | 共通部 |
| `inc/icons.php` | インライン SVG アイコン |
| `inc/illustrations.php` | インライン SVG イラスト・アクセス略図 |
| `inc/meta.php` | メタ情報 |
| `style.css` | 全スタイル（フレームワーク不使用） |

- 写真は1点（Pexels）だけ。アイコンとイラストは SVG で自作しています。
  ストックフォトを並べるより、**利用条件がはっきりしている**ためです。
- 所在地が架空なので、実地図ではなく位置関係の略図を SVG で描いています。
- カスタマイザーの設定項目にはすべて `sanitize_callback` を付けています
  （付けないと WordPress は値を保存しません）。
- 文言は柔道整復の広告ガイドラインを踏まえ、「治療」ではなく「施術」で統一しています。
  整骨院は医療機関ではないため「治療」は使えません。

## plugins/clinic-fee-simulator

症状・保険種別・通院頻度を選ぶと、窓口でのお支払いの目安をその場で表示します。

| ファイル | 役割 |
|---|---|
| `includes/class-clinic-fee-simulator-settings.php` | 管理画面（料金表・注記の編集） |
| `includes/class-clinic-fee-simulator-calculator.php` | 計算ロジックと入力の正規化 |
| `includes/class-clinic-fee-simulator-shortcode.php` | `[clinic_simulator]` |
| `includes/class-clinic-fee-simulator-rest.php` | `POST /wp-json/clinic-simulator/v1/estimate` |
| `assets/simulator.js` / `.css` | 表示と即時更新 |

設計上の判断:

- **計算はサーバー側で必ずやり直します。** ブラウザから送られてきた金額は使いません。
  初期表示は PHP でレンダリングし、JS は「選び直したときに即座に反映する」役だけです
  （プログレッシブエンハンスメント）。
- **入力は許可リストで正規化します。** 想定外の値は既定値に落とし、症状は
  許可された ID との積集合だけを残します。
- **料金表・端数・注記は管理画面から編集できます。** 数字をコードに埋めていません。
- 保険3種 × 頻度3種 × 症状0〜5個の**全54通り**を検算し、すべて一致することを確認しています。

## plugins/clinic-contact

| ファイル | 役割 |
|---|---|
| `includes/class-clinic-contact-mailer.php` | `phpmailer_init` で SMTP へ切り替え |
| `includes/class-clinic-contact-form.php` | `[clinic_contact]` |
| `assets/contact.css` | スタイル |

- スパム対策は nonce ＋ ハニーポット ＋ 時間トラップ（送信までが速すぎる投稿を弾く）。
- **SMTP が未設定なら送信せずに失敗させます。** PHP の `mail()` に落とすと
  SPF / DKIM が付かず、届いても迷惑メールに入るためです。
- **SMTP AUTH のログイン ID と差出人アドレスは別の定数に分けています。**
  この2つは同じとは限りません（実際に別でした）。
- SPF / DKIM / DMARC を通し、受信側で迷惑メール判定を受けないことを実測で確認しています。

## 必要な設定

`wp-config.php` に以下を定義します（値はリポジトリに含めません）。

```php
define( 'CLINIC_SMTP_HOST', 'mail.example.net' );
define( 'CLINIC_SMTP_PORT', 465 );
define( 'CLINIC_SMTP_USER', 'ログインID' );        // 差出人アドレスとは別
define( 'CLINIC_SMTP_FROM', 'info@example.net' );  // SPF/DKIM の対象
define( 'CLINIC_SMTP_PASS', '...' );
```

## 動作確認

- PC 1440px / スマートフォン 390px の両方で、横方向のはみ出しとコンソールエラーが
  0件であることをヘッドレスブラウザで実測。
- シミュレーターの全54通りを REST エンドポイント経由で検算。
- 不正な入力（未知の保険種別・範囲外の頻度・配列でない症状・`<script>`）が
  既定値に落ちることを確認。

## 品質の確認

push のたびに GitHub Actions で次を回しています。

| ジョブ | 内容 |
|---|---|
| 構文チェック | PHP 8.0 / 8.1 / 8.2 / 8.3 / 8.4 で `php -l` |
| WordPress Coding Standards | PHPCS + WPCS + PHPCompatibilityWP（`phpcs.xml.dist`） |
| テスト | PHPUnit。同じ PHP マトリクス |

デプロイは入れていません。サーバー側が作業ツリーの実体で、
変更は サーバー → 手元 → GitHub の向きに流れます。
自動デプロイは逆向きになり、サーバー上の変更と衝突します。

### テスト

料金の計算と設定値の検証は WordPress のデータベースを触らないため、
WordPress のテストスイートも MySQL も使いません。実際に呼ばれる数個の
関数だけを `tests/bootstrap.php` で用意しています。実行は 0.01 秒ほどです。

- `tests/CalculatorTest.php` … 入力の正規化と、保険3種 × 頻度3種 × 症状0〜5個の
  **全54通り**を、独立して立てた式と突き合わせます
- `tests/SettingsTest.php` … 管理画面の入力検証。数字でない値・負数・範囲外を渡し、
  その項目だけが据え置かれること、他の項目は保存されることを確認します

このテストは実際に2件のバグを見つけました。`frequency` に `-2` を渡すと
`absint()` が 2 に変えて「週2回」として通っていた件と、自己負担割合を 25% に
設定すると注記に「3割」と表示されていた件です。どちらも手作業の確認では
値の選び方によって隠れていました。

### PHPCS で外している規則

`phpcs.xml.dist` に理由を書いてあります。要点は、コメントの終止符や
先頭の大文字を見る規則は日本語で書いている限り構造的に通らないこと、
`$phpmailer->SMTPAuth` などは PHPMailer 側の命名で改名できないことです。

## 国際化について

文字列は `__()` / `esc_html_e()` で包み、`.pot` を各パッケージの `languages/` に置いています
（`wp i18n make-pot` で生成）。テーマは `load_theme_textdomain()`、プラグインは
`load_plugin_textdomain()` で読み込みます。

**msgid は日本語のままにしています。** このサイトの読み手も編集する人も日本語話者で、
テンプレート中に英語の msgid が挟まると読みにくくなるためです。
翻訳を足す場合は、日本語 msgid に対する訳として書きます。

## ライセンス

GPL-2.0-or-later（`LICENSE`）。WordPress のテーマ・プラグインは WordPress 由来のコードを
使うため、この条件になります。

## 生成AIの利用について

コード、SVG のアイコン・イラスト、文案の下書きに生成AIを使っています。
要件の整理、設計上の判断、動作検証、サーバー構築は制作者が行いました。
