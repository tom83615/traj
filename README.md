#風場模式模擬

*english version README will later upload. (maybe will)*
這是配合大學專討所做的試作品，基本上省略非常多項目，僅供參考。
> 前提假設

>1.  風的變化是連續性的 
>2.  只考慮邊界以內的點 
>3.  不考慮地形起伏 
>4.  只適用於地表風

有興趣了解其運作內容可以看[此投影片](http://www.slideshare.net/tom83615/ss-53119639)

##以下說明各資料夾內容及檔案

###other

####datatosql.php
這是一個將環保署的空品資料輸入至資料庫的範例，
如果不是使用相同資料，
或資料庫已經建立完畢，
請跳過他。

###sample

####datainput.php
這份文件中的函式是將資料庫資料取回，
並排列至主程式所需格式的liberary。

#### datainput_crawler.php
這份文件中的函式是將環保署的每小時空品資料抓回，
在**不建立資料庫**時可以採行的**替代方案**，
並排列至主程式所需格式的liberary。

#### mapping.php
以網格座標實作台灣區域的風場，
橫向縱向各分割20個點，
產生出R語言程式碼(請先安裝RgoogleMaps)。
使用以下方法

>取用資料庫資料：
>http://[主機]/[資料夾]/sample/mapping.php?t=[yyyy-mm-dd tt:00]

>使用爬蟲取回資料：
>http://[主機]/[資料夾]/sample/mapping.php?t=now

#### sample_crawler.php
指定單一經緯度使用爬蟲取回即時資料，
回傳資料為

1.    "ws"：風速
2.    "wd"：風向(方向角)
3.    "num\_of\_site"：資料庫內測站數量
4.    "sel\_of\_site"：被使用測站數量
5.    "sel_error"：相對誤差
6.    "all_site"：資料庫內所有測站名稱
7.    "selected_site"：使用的測站名稱

使用以下方法
>http://[主機]/[資料夾]/sample/sample_crawler.php?lat=[緯度]&lon=[經度]

#### sample_sql.php
指定單一經緯度使用資料庫資料，
回傳資料為：

1.    "ws"：風速
2.    "wd"：風向(方向角)
3.    "num\_of\_site"：資料庫內測站數量
4.    "sel\_of\_site"：被使用測站數量
5.    "sel_error"：相對誤差
6.    "all_site"：資料庫內所有測站名稱
7.    "selected_site"：使用的測站名稱

使用以下方法
>http://[主機]/[資料夾]/sample/sample_sql.php?lat=[緯度]&lon=[經度]&date=[yyyy/mm/dd]&time=[0-23]&wh=[風場高度]

###testfile

####dayreport.php
取得資料庫中指定一天所有測站的模擬值與實際值之比較，及相關係數。

使用以下方法
>http://[主機]/[資料夾]/testfile/dayreport.php?date=[yyyy-mm-dd tt:00]

####dayreport_r.php
取得資料庫中指定一天的各測站相關係數(較簡略)。
使用以下方法
>http://[主機]/[資料夾]/testfile/dayreport_r.php?date=[yyyy-mm-dd tt:00]

####random.php
隨機抽取指定數量之天數，
進行模擬值與實際值之比較，及相關係數。

使用以下方法
>http://[主機]/[資料夾]/testfile/random.php?n=[指定數值]

###windmap

####class.php
用於測站的類別設定，分為Location、Site、Target：

*    Location：以下兩類別的父類別，儲存經度、緯度、風向、風速，並定義距離運算。
*    Site：儲存測站的類別，額外儲存採風點高度(高度)、權重，運算各項權重及修正係數CF。
*    Target：儲存目標點的類別，額外儲存高度，運算最後權重與測站資料相乘與加總

####statistics.php
用於計算相關係數

####windengine_lib.php
這個檔案有4個子程式：

*    wpp_law：用來對不同高度風速進行修正。
*    concentrat：用來計算距離內側站數量，密集度權重所需要的參數。
*    anglecorrect：將角度修正至0至360度間。
*    windengine：主程式，包含初始化，選擇係數SF是否跳出迴圈比較。
