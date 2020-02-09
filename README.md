# PHP wikipedia articles downloader & PDF converter

## The main purpose is to obtain & convert & download as PDF-files the articles, contained within certain div in a given initial Wiki-article, passed via input. 
## Example:
![Снимок экрана от 2020-02-09 15-44-38](https://user-images.githubusercontent.com/48214249/74102323-41fdbd80-4b4b-11ea-9ebb-78a492778fe6.png)


### Modus operandi
1. Processes an URL passed from the input.
```php
$url = urldecode($_POST["initialArticle"]);
```
Creates a DB table title removing wrong charachters
```php
 $tableName = substr($url, strpos($url, 'wiki/') +5);
 $tableName = str_replace("(", "_", $tableName);
 $tableName = str_replace(")", "_", $tableName);
 $tableName = str_replace(",", "_", $tableName);
 ```
 
 2. Checks if the table exists already in DB: if the table is not empty, prints out the URLs contained in the table.
 If table doesn't exist, creates it and records the URLs into the table. 
 
 3. Then downloads PDFs.
 