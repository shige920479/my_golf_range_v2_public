# my_golf_range_v2

ゴルフ練習場の予約管理システムです。  
ChatGPTのサポートを得ながらの「リファクタリング版」になります。（v1はオブジェクト指向とは言い難い内容でした...）  
フレームワークは使用せず、素のPHPで MVC構成・DI・独自Router の実装にチャレンジしてみました。

### 公開URL :  <a href="https://portfolio-sh0212.com/my_golf_range" target="_blank">https://portfolio-sh0212.com/my_golf_range</a>

---

## 機能一覧

- ユーザー側予約フロー
  - 仮登録（メール認証前提）
  - 本登録
  - 予約枠検索
  - 予約登録
  - 予約一覧・変更・キャンセル
- 管理者側機能
  - 初期登録 
  - 料金設定
  - メンテナンス登録

※ この公開リポジトリは「ソースコード閲覧用」です  
※ 本番（Xserver）にデプロイ済みです（実行可能）  

  | ユーザー | メールアドレス | password |
  |----------|---------|---------|
  | ユーザー | test1@mail.com | pass123 |
  | オーナー | owner@mail.com | owner123 |
  * ユーザーは test1@mail.com～test5@mail.com まで登録済み
  > <u>オーナーの初期設定画面は閲覧のみ</u>としてください  
   <a href="https://portfolio-sh0212.com/my_golf_range/owner/login" target="_blank">https://portfolio-sh0212.com/my_golf_range/owner/login</a>
---

## 技術構成

|   項目    | 内容     |
|---------- |---------|
| 開発環境  | Docker |
| 言語      | PHP 8.3 |
| Webサーバ | Apache |
| DB       | MySQL |
| Router   | 自作、動的パラメータ対応 |
| DI       | 自作簡易Container |
| 画像処理  | 未 |

---

## ディレクトリ構成（抜粋）
app/  
├ Controller/  
├ Model/  
├ Repository/  
├ Service/  
└ Views/  
public/  
routes/  
storage/  

---

## テスト
ユーザー側の主要機能(*1)は PHPUnit でテストしています（tests ディレクトリ参照）  
  *1: Service層、Repository層

---

## 今後の予定

- 管理者機能の見直し
- 機能拡充
- Laravel版のリファクタリング