# F-RevoCRM 7.3

F-RevoCRM は日本企業に合わせた形で開発された高機能なCRMです。
あらゆる顧客接点を管理するために、キャンペーン・リード管理から顧客・商談管理、販売管理、サポート管理・プロジェクト管理まで幅広い機能を持ち合わせています。

# ライセンス
Vtiger Public License 1.2

## サーバ推奨要件
* 2コア以上、4GB以上のメモリ、40GB以上の空き容量（利用人数・用途によってスペックが大幅に変わる）
* Apache 2.4以上
* PHP 5.6 / 7.2以上（8.0以上は除く）
  * php-imap
  * php-curl
  * php-xml
  * memory_limit = 512M(min. 256MB)
  * max_execution_time = 0 (min. 60 seconds、0は無制限)
  * error_reporting (E_ERROR & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED)
  * display_errors = OFF
  * short_open_tag = OFF
* MySQL 5.6以上
  * storage_engine = InnoDB
  * local_infile = ON (under [mysqld] section)
  * sql_mode = NO_ENGINE_SUBSTITUTION for MySQL 5.6+

## F-RevoCRMへのアクセスについて
本アプリケーションはWebアプリケーションとなりますので、URLへ直接アクセスしてください。  
またリファラーチェックを行っておりますので、もしSharePointなどの社内イントラにリンクを設置する場合は、`rel=noreferrer`属性を追加してください。
```
<a href="https://example.com/{CRM_DIR}/index.php">F-RevoCRM</a>​​​​​​
↓
<a href="https://example.com/{CRM_DIR}/index.php" rel="noreferrer">F-RevoCRM</a>​​​​​​
```

## PCの推奨環境
* Windows 10 Google Chrome最新 / Microsoft Edge(Chronium)最新 / Internet Explorer 11（2022年4月 非推奨に移行予定）
* 最低1366×768以上の解像度、推奨1920×1080以上
* 最低Intel Core iシリーズまたはそれ以上の2コア以上のプロセッサ、推奨4コア以上
* 最低4GB以上のメモリ、推奨8GB以上

## モバイルデバイスの推奨環境
* Android 9.x/8.x Google Chrome（タブレット未確認）
* iPhone iOS 12.x/11.x Safari（iPad未確認）

## インストール方法（概要）
以下、F-RevoCRMのインストール方法になります。

* F-RevoCRM7.3のインストール方法はそのまま読み進めてください。
* F-RevoCRM6.5からのバージョンアップはインストール方法の後に記載があります。
* F-RevoCRM7.3のパッチ適用方法については各パッチ付属のREADMEを参照してください。
* 本レポジトリをDockerで構築する場合は、[docker/README.md](./docker/README.md)を参照してください。

### configファイルを独自に設定する場合
configファイルは`config.inc.php`として、インストール後に生成されます。  
このファイルは、`config.template.php`をベースに、インストーラーが自動生成するファイルとなりますので、もし独自に設定する必要がある場合は`config.template.php`を`config.inc.php`にリネームし、利用してください。

### 前提条件
データベース名などを「frevocrm」としてインストールすることを前提に記載します。

### 1. Apache, PHP, MySQLのインストール
事前にそれぞれをインストールしておいてください。

***注意点1**

MySQLのSTRICT_TRANS_TABLESを無効にしてください。
```
# 下記手順は設定例

vi /etc/my.cnf

# 以下の行を変更
[変更前]
sql_mode=NO_ENGINE_SUBSTITUTION,STRICT_TRANS_TABLES

[変更後]
sql_mode=NO_ENGINE_SUBSTITUTION

# mysqlを再起動
service mysqld restart
```

MySQL8.0以降の場合は、認証モードの変更が必要です。
```
vi /etc/my.cnf

# [mysqld]のセクションの中に以下の1行を追加
default-authentication-plugin=mysql_native_password

# mysqlを再起動
service mysqld restart
```

***注意点2**

php.iniにて以下の設定が必要です。
```
date.timezone = "Asia/Tokyo"
max_input_vars = 100000
post_max_size = 32M
upload_max_filesize = 32M
max_execution_time = 60
```
* 最低要件のため、利用用途等に合わせて数値を大きくしてください。

### 2. F-RevoCRMのZIPファイルを解凍、設置

ApacheのDocumentRoot以下に解凍したディレクトリ毎、あるいはファイルを置いて下さい。
ここでは仮に/var/www/frevocrmに設置したものをとして進めます。

### 3. 初期設定

3.で設置したF-RevoCRMのURLを開きます。
* http://example.com/frevocrm

画面に従って初期設定を完了させてください。


## バージョンアップ方法
F-RevoCRM 6.5 を F-RevoCRM 7.3 にバージョンアップする手順になります。

### 前提条件
* F-RevoCRM 6.5 であること（パッチバージョンは問わない）
* ソースコードの修正やモジュールの追加がされていないこと
* F-RevoCRM 6.5のインストール済み環境があること

### 1. バックアップの取得
F-RevoCRMのデータベース、ファイルを全てバックアップを取得します。

### 2. プログラムファイルの置き換え
1. F-RevoCRMのディレクトリ全体を別名に置き換えます。
```
# コマンド例
mv frevocrm frevocrm.20201001
```
2. F-RevoCRM 7.3 インストール直後のファイルをもともとのF-RevoCRMのディレクトリとしてコピーします。
```
# コマンド例
cp -r frevocrm73 frevocrm
```
3. F-RevoCRMの設定ファイル(config.*, *.properties, *tabdata.php)をコピーします。
```
# コマンド例
cp frevocrm.20201001/config.* frevocrm/
cp frevocrm.20201001/*.properties frevocrm/
cp frevocrm.20201001/*tabdata.php frevocrm/
```
4.F-RevoCRMのドキュメントファイルをコピーします。
```
# コマンド例
cp -r frevocrm.20201001/storage/* frevocrm/storage/
```

### 3. マイグレーションツールの実行
タグとしてv7.3.xが追加されるまで、Migrationは実行されません。  
最新のバージョンで実行したい場合は、`vtigerversion.php`のファイルを編集し、次のバージョンを指定してから以下のマイグレーション用のURLを実行してください。

1. アクセスすると自動でマイグレーションが実行されます。
 * http://example.com/frevocrm/index.php?module=Migration&view=Index&mode=step1

2. 動作確認
  F-RevoCRMのログインや業務に関わる動作を確認してください。

3. 作業ディレクトリの削除
```
# コマンド例
rm -r frevocrm.20170118
```

## 開発環境の構築
Dockerで構築する為、[docker/README.md](./docker/README.md)を参照してください。  

### xdebug
xdebug3がインストール済みです。
`docker-compose.yml` の以下の部分を修正してください
```yml
# Xdebugの設定を有効にしたい場合は、mode=debug に変更してください
# XDEBUG_CONFIG: "mode=off client_host=host.docker.internal client_port=9003 start_with_request=yes"
XDEBUG_CONFIG: "mode=debug client_host=host.docker.internal client_port=9003 start_with_request=yes"
```
#### WSL2での利用
WSL2を利用の場合は、以下のように実行してください。
```sh
cp docker-compose.override.yml.exmple docker-compose.override.yml
cp .env.example .env
```
その後、.envの中にWSL2のIPアドレスを入力してください。
```sh
hostname -I
# 172.26.76.74
vim .env
# DOCKER_HOST_IP=172.26.76.74
```
#### VSCodeでの設定
vscodeをご利用の場合は、xdebugのエクステンションをインストール後、以下のように `.vscode/launch.json`を修正してください。
```json
{
  "version": "0.2.0",
  "configurations": [
    {
      "name": "F-RevoCRM XDebug:9003",
      "type": "php",
      "request": "launch",
      "port": 9003, 
      "pathMappings": {
        "/var/www/html": "${workspaceRoot}"
      }
    }
  ]
}
```

## 更新履歴

### F-RevoCRM7.3.4
#### パッチ適用方法
- 差分ファイルを上書き更新してください
- 以下のURLにアクセスし、マイグレーションを実施してください。  
`https://example.com/frevocrm/index.php?module=Migration&view=Index&mode=step1`

#### 主な変更点

* 機能改善
  - #79 プロジェクトタスク「終了日」のクイック作成をデフォルトで有効にしてほしい
  - #194 プロジェクトの関連>マイルストーンで新規追加する際、プロジェクトを自動セットしてほしい
  - #155 Migration後の画面が英語表示
  - #98 日付条件の翻訳を改善

* 不具合修正
  - #244 見積の「項目の詳細」から送料を設定しても反映されない
  - #234 顧客企業の活動が二重表示されてしまう
  - #220 カスタマーポータルからファイルをダウンロードすると、空のファイルが送られる
  - #216 関連を開くとPHPのエラーが発生する
  - #205 TODO管理において、ユーザ選択およびステータスが保持されない
  - #201 00:00開始の予定を作成しようとすると終日にチェックが入ってしまう
  - #200 カレンダーの設定を12時間表記にすると、週次カレンダー上部の「終日エリア」から予定を作成するときに、終日フラグが入らない
  - #199 リストの複製をすると、項目と並び順の選択に余計な項目が設定される
  - #192 レポートで日付項目に対して「空である」などの条件を指定するとSQLエラーが発生する
  - #183 「ユーザー名の変更」モーダルの新ユーザー名が翻訳されていない
  - #181 「ユーザー名の変更」がパスワードがエラーで保存出来ない
  - #179 関連画面のコメントで編集を行うと改行が削除されたように見える
  - #173 削除した顧客担当者が関連の活動に残る
  - #172 コメントに自身が設定しているサムネイル画像（プロフィール画像）が表示されない
  - #169 作成した見積の合計金額と出力したPDFファイルの合計金額が1円違う
  - #161 システム設定画面でヘッダーが一部ずれる
  - #159 エクスポートした見積PDFの値引き額（貴社特別値引き）に反映されない
  - #130 F-RevoCRM6.Xから7.Xにマイグレーションした後、F-RevoCRM6.Xにて作成していた活動を削除すると、全活動が削除される
  - #123 項目タイプ関連、モジュール活動の項目で参照ボタンを押したら「権限がありません」と表示される。
  - #78 フィルターのデフォルト設定の不具合
  - #67 モジュールの詳細画面にて、コメントのアイコンサイズが小さい
  - #158 画面幅が狭いときドキュメントのアップロードモーダルが崩れる
  - #134 画面幅が狭い際にドキュメントとレポートでヘッダーがずれる
  - #213 ユーザーとユーザで表記ゆれがある

* その他修正
  - typo関連の修正
  - Migration時に実行時間の上限がなくなるように修正
  - #180 コミットメッセージの表記ゆれの改善

### F-RevoCRM7.3.3
#### パッチ適用方法
- 差分ファイルを上書き更新してください
- 以下のURLにアクセスし、マイグレーションを実施してください。  
`https://example.com/frevocrm/index.php?module=Migration&view=Index&mode=step1`

#### 主な変更点

* 機能改善
  - 選択されているリストの色を、見やすくなるように濃く変更（#139）
  - カレンダー通知用ポップアップの処理速度を改善（#99）
  - Webフォーム参照フィールドの値を、cf_xxx形式で表示するように改善（#91）
  - 関連項目として「ユーザー」を設定できるように改善（#32）
  - 初期インストールされるワークフローのワークフロー名を日本語に変更
  - 初期インストールされるレポートのレポート名を日本語に変更

* 不具合修正
  - カレンダーの招待ユーザーに送付されるicsファイルのタイムゾーンを、受信するユーザーに合わせるように修正（#121）
  - 上部検索エリアが適切に動かないケースの修正（#85, #80）
  - インライン編集を行い、キャンセルを行った後に再度編集を行い保存すると正常な値が保存されない不具合の修正（#95）
  - 顧客ポータルのURLが長い場合、枠をはみ出してしまう不具合の修正（#64, #61）
  - 繰り返し予定や招待予定を作成した場合、終日フラグが外れてしまう不具合の修正（#96）
  - ダッシュボードウィジェットのノートにて、URLに？が含まれている場合に切り取られて保存されてしまう不具合の修正（#48）
  - 活動に顧客担当者を複数名登録した後、詳細入力へ遷移すると顧客担当者が消えてしまう不具合の修正（#10）
  - 終日の予定を時間予定に変更した際に、終日フラグがはずれない不具合の修正
  - PDFを一括出力した場合に、顧客企業名をファイル名に含むように修正
  - デザイン調整（#114, #140, #125, #116, #83, #97, #71, #37, #33）、その他

* その他修正
  - 復数の日本語訳を追加（#72） 
  - DockerコンテナのタイムゾーンをJSTに変更（#154）
  - Docker環境でのインストール時の入力を簡易化するように修正
  - Docker環境下で必要なフォルダが生成されない不具合の修正
  - Docker環境にxdebug3をインストール
  - Docker環境を再起動時に自動で立ち上がるように修正
  - Pull Requestのテンプレートを作成
  - F-RevoCRMのIE11対応を、2022年4月以降非推奨とする文言の追加


### F-RevoCRM7.3.2
#### パッチ適用方法
- 差分ファイルを上書き更新してください
- 以下のURLにアクセスし、マイグレーションを実施してください。  
`https://example.com/frevocrm/index.php?module=Migration&view=Index&mode=step1`

#### 主な変更点

* 機能改善
  - ユーザーの初回ログイン時に、共有カレンダーのマイグループに所属するユーザーを、自身のみになるように改善
  - 製品モジュールのエクスポート画面の日本語和訳を追加(#31)
  - 項目編集の｢項目を表示｣の日本語和訳を追加(#43)
  - ドキュメントモジュールで「内部」としてURLを保存した際に、新しいタブでページが開くように修正
  - その他、ドキュメントでURLを指定したときの動作を改善
  - 見積、受注、発注、請求モジュールの編集画面にて、手数料が数値以外の場合は0円を設定するように修正
  - カレンダー表示画面にて、マウスオーバー時に表示される活動画面に「活動コピー」機能を追加

* 不具合修正
  - README.mdのアップデート手順に誤りがあったため修正
  - 概要欄や関連から活動を追加する際に、招待者が正常に登録されない不具合を修正
  - スマートフォン表示時に詳細画面で項目タイトルが右寄せになる不具合の修正(#2)
  - ドキュメントの詳細画面でURLを内部で保存するとハイパーリンクにならない問題を修正
  - 見積、受注、発注、請求モジュールの編集画面にて、課税対象地域を変更すると金額がNaNとなる不具合を修正
  - リストの関連リンクから別モジュールへ遷移した場合、左サイドバーのメニューがMARKETINGのものになる不具合の修正(#38)
  - 編集画面においてシステム管理者でないユーザーの場合、詳細情報の配置が崩れる問題を修正
  - Docker環境にてドキュメント保存用フォルダが無いことによってファイルアップロードができない不具合の修正(#5)
  - Docker環境にてJpegファイルがアップロードできない不具合の修正(#24) @zeroadster

* その他修正
  - README.mdに記載されているサンプルURLをexample.comに置換
  - インストーラーにて、環境変数を見て自動でDB設定が入るように修正

### F-RevoCRM7.3.1
#### パッチ適用方法
- 差分ファイルを上書き更新してください
- 以下のURLにアクセスし、マイグレーションを実施してください。  
`https://example.com/frevocrm/index.php?module=Migration&view=Index&mode=step1`

#### 主な変更点
* 機能改善
  - 日本語翻訳を複数追加
  - サイドバーアイコン部分の表示を改善
  - 個人アイコンを設定した際のアスペクト比を維持するように改善
  - チケットモジュール、FAQモジュールにて、画像を挿入した場合の表示を改善
  - チケットモジュール・FAQモジュールにて、画像が保存できないケースを改善
  - チケットモジュール・FAQモジュールにて、「イメージ」ボタンのプレビュー欄の表示を改善
  - 日報モジュールの「コメント」項目の文字数制限が250文字だった為、TEXT型に変更
  - コメント欄にて、PDFファイルをアップロードした時のプレビューの挙動を改善
  - プロジェクトモジュールの「チャート」画面にて、タスク名表示を改善
  - 活動登録時に時間の選択肢をキーボードで選択した場合の動作を改善
  - モバイル：日報モジュールの概要画面の更新履歴エリアにて、更新日時の表示を改善

* 不具合修正
  - 各モジュールの概要画面に表示される活動の曜日が全て木曜日となる不具合の修正
  - 一覧画面にて、最終更新者のユーザーを選択できない不具合の修正
  - メール送信可能な一覧からメール送信対象をチェックを付けてメール送信を行った際に、ランダムに1通のみメールが送信される不具合の修正
  - リード画面にて、関連メニューのメール部分に件数が表示されない不具合の修正
  - 一般ユーザーで、レポート表示時にエラーが発生する不具合の修正
  - 管理機能「企業の詳細」画面にて、画像がアップロードできないケースがある不具合の修正
  - 初期セットアップ時に日報が追加されない不具合の修正 ※本パッチ適用時に日報モジュールが追加されます

### F-RevoCRM7.3
#### 主な変更点

* 機能追加
  - 見積、受注、請求、発注のPDFテンプレートを作成・編集できる機能を追加
  - システム設定に文言変更機能を追加
  - プロジェクトタスクのガントチャートを追加
  - グラフのダッシュボード表示を追加
  - デフォルトのダッシュボードを追加
  - ユーザー一覧に検索機能を追加
  - ユーザーのCSVインポート機能を追加
  - 関連データに対しての簡易検索機能を追加
  - Webフォーム取り込みに添付ファイルの取り込み機能を追加
  - 一覧画面にクイック表示の機能を追加
  - 案件からプロジェクトにコンバートできる機能を追加
  - メールコンバーターのメール自動紐付け機能を追加
  - フォロー機能を追加
  - RSS、ブックマーク機能を追加（復活）

* 機能改善
  - 画面デザインを刷新
  - 各文言を一般的な用語に変更
  - ユーザーのパスワードを8文字以上、アルファベット英数記号を含めるように制限
  - 一覧のスクロール時にヘッダ行が固定されるように改善（一覧画面のみ）
  - チケット（旧サポート依頼）、FAQ（旧回答事例）のテキストエリアの入力欄をリッチテキストに変更
  - 項目の種類に「関連」を追加（他モジュールを紐付ける項目）
  - リスト（旧フィルタ）の複製できるように改善
  - 共有リスト（旧フィルタ）の共有先の設定できるように改善
  - 「登録/編集」権限を「登録」と「編集」の権限に分離
  - 活動のCSVインポート機能を追加
  - 複数のダッシュボード管理に対応
  - ダッシュボードのウェジェットの表示サイズ変更を追加
  - 主要項目（旧概要）と関連一覧の表示設定の柔軟性を強化
  - 課税計算の設定を強化
  - タグ機能（旧タグクラウド）を強化
  - 関連するコメントをすべて表示できるように改善
  - 各レコードの入力元の表示を追加
  - 活動の繰り返し登録をした際に一括で削除や変更ができるように改善
  - ワークフローのレコードの登録、レコードの更新のアクションを強化
  - 初期表示のカレンダー表示（個人、共有、リスト）が選択できるように改善

以上

