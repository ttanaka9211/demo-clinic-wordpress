=== Clinic Fee Simulator ===
Contributors: hatchdogs
Tags: shortcode, calculator, clinic
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

整骨院向けの施術費用シミュレーター。ショートコードで設置でき、料金表は管理画面から編集できます。

== Description ==

保険の種類・通院の頻度・症状の数から、施術費用の目安を表示します。

* ショートコード `[clinic_simulator]` で任意の場所に設置
* 料金表（基本施術料・加算・自己負担割合・注記）は「設定 > 料金シミュレーター」から編集
* テーマを問わず動作します（テーマ側に CSS カスタムプロパティがあれば配色を引き継ぎます）

= 設計について =

* **サーバ側が正**：画面の JavaScript でも計算しますが、表示する数字は REST 経由でサーバが計算し直した値で上書きします。画面から送られた数字は信用しません。
* **JavaScript なしでも動く**：ショートコードの時点でサーバが初期値を計算して描画するため、JS が無効でも金額が表示されます。JS は即時更新を足しているだけです。
* **入力は必ず正規化**：保険種別は許可リスト、頻度と症状は範囲・許可キーで丸めます。想定外の値は既定値に落とします。
* **設定値も検証**：管理画面の入力は範囲チェックを行い、外れた場合はエラーを表示して既定値へ戻します。

== Installation ==

1. `clinic-fee-simulator` を `wp-content/plugins/` に置く
2. プラグインを有効化する
3. 「設定 > 料金シミュレーター」で料金表を調整する
4. 固定ページや投稿、テンプレートに `[clinic_simulator]` を書く

== Frequently Asked Questions ==

= 表示される金額は正確ですか =

目安です。保険の適用可否や施術内容によって実際の費用は変わります。注記の文言は管理画面から変更できます。

== Changelog ==

= 1.0.0 =
* 初回リリース。
