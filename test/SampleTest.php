
<?php

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverSelect;

class SampleTest extends TestCase
{
    protected $pdo; // PDOオブジェクト用のプロパティ(メンバ変数)の宣言
    protected $driver;

    public function setUp(): void
    {
        // PDOオブジェクトを生成し、データベースに接続
        $dsn = "mysql:host=db;dbname=shop;charset=utf8";
        $user = "shopping";
        $password = "site";
        try {
            $this->pdo = new PDO($dsn, $user, $password);
        } catch (Exception $e) {
            echo 'Error:' . $e->getMessage();
            die();
        }

        #XAMPP環境で実施している場合、$dsn設定を変更する必要がある
        //ファイルパス
        $rdfile = __DIR__ . '/../src/classes/dbdata.php';
        $val = "host=db;";

        //ファイルの内容を全て文字列に読み込む
        $str = file_get_contents($rdfile);
        //検索文字列に一致したすべての文字列を置換する
        $str = str_replace("host=localhost;", $val, $str);
        //文字列をファイルに書き込む
        file_put_contents($rdfile, $str);

        // chrome ドライバーの起動
        $host = 'http://172.17.0.1:4444/wd/hub'; #Github Actions上で実行可能なHost
        // chrome ドライバーの起動
        $this->driver = RemoteWebDriver::create($host, DesiredCapabilities::chrome());
    }

    public function testLogin()
    {
        // 指定URLへ遷移 (Google)
        $this->driver->get('http://php/src/index.php');

        // トップページ画面のpcリンクをクリック
        $element_a = $this->driver->findElements(WebDriverBy::tagName('a'));
        $element_a[4]->click();

        // ジャンル別商品一覧画面の詳細リンクをクリック
        $element_a = $this->driver->findElements(WebDriverBy::tagName('a'));
        $element_a[4]->click();

        // 商品詳細画面の注文数を「2」にし、「カートに入れる」をクリック
        $selector = $this->driver->findElement(WebDriverBy::tagName('select'));
        $selector->click();
        $this->driver->getKeyboard()->sendKeys("2");
        $selector->click();
        $selector->submit();

        // ヘッダーのpcリンクをクリック
        $element_a = $this->driver->findElements(WebDriverBy::tagName('a'));
        $element_a[3]->click();

        // ログイン
        $element_input = $this->driver->findElements(WebDriverBy::tagName('input'));
        $element_input[0]->sendKeys('kobe@denshi.net');
        $element_input[1]->sendKeys('kobedenshi');
        $element_input[2]->submit();

        // ヘッダーのカートをクリック
        $element_a = $this->driver->findElements(WebDriverBy::tagName('a'));
        $element_a[2]->click();

        // カート画面に遷移し、商品の注文数を取得
        $element_select = $this->driver->findElement(WebDriverBy::tagName('select'));
        $select = new WebDriverSelect($element_select);
        $selector_text = $select->getFirstSelectedOption()->getText();

        // assert
        $this->assertEquals(2, $selector_text, 'ログイン後、カート内の商品注文数が一致していません');
    }
}
