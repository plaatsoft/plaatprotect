Installation manual plaatprotect
===============================

Login on Raspberry Pi with user pi

### Step 1 - Install following depending thirdparty software packages
sudo apt-get install apache2
sudo apt-get install php5
sudo apt-get install python
sudo apt-get install mysql-server
sudo apt-get install python-mysqldb
sudo apt-get install fswebcam

### Step 2 . Create mysql plaatprotect database
mysql -u root -p
CREATE DATABASE plaatprotect;
GRANT ALL ON plaatprotect.* TO plaatprotect@`127.0.0.1` IDENTIFIED BY `plaatprotect`;
FLUSH PRIVILEGES;
QUIT;

### Step 3. Download plaatprotect from plaatsoft.nl.
Copy zip file to /tmp
login on the raspberry with user `pi`
cd /var/www/html
sudo cp /tmp/plaatprotect.zip .
sudo unzip *.zip
sudo chmod a+wrx /var/www/html/plaatprotect/backup
sudo chmod a+wrx /var/www/html/plaatprotect/webcam

### Step 4. Create config.inc with correct database settings
cp config.inc.sample config.inc
	 
### Step 5. Add the following cron job:
crontab -e
* * * * * cd /var/www/html/plaatprotect/cron.php; php cron.php

### Step 6. Go to http://[raspberry-ip]/plaatprotect.
Select setting page and customize plaatprotect to your personal needs!

### Step 7. Installation is now ready
Now every minute the energy, gas, (optional) solar and (optional) 
weather station data is fetch and processed.

If there are any questions please let me known!
	 
wplaat
info@plaatsoft.nl
