# Slim-Ionic-Server
* Slim framework as backend services for ionic simple store
* Simple auth signin/signup and provide jwt for ionic app
* Simple features like add product/brand/category, wishlist, checkout, invoice/shipping(poslaju)

##### Instruction(run locally only)
* **This built only for Malaysia usage**
* Default admin login credentials arma7x@live.com 111111111
* Run composer install to download all dependencies
* Set your own configuration in file /app/settings.php
* Import xxx.sql to your database
* Create sessions folder in Slim-Ionic-Server installation folder
* To run using php built-in webserver just, open terminal, cd into Slim-Ionic-Server installation folder and type 'php -S 127.0.0.1:2000 -t public' then press enter
* If you want to change those address and port, please edit file **/app/settings.php(at line::23)** by using your own preferences
* **Ionic app repo link, https://github.com/arma7x/Slim-Ionic-Store**
