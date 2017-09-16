Infium Server (PHP)
===================

Installation
------------
1. Open the config.php file under the "api" directory. Set the following variables:
   - $baseUrl - This should be the public URL on your webserver to the api directory.
   - $emailFrom - This should be the e-mail address that the application should use as the sender address.
   - $databaseDSN - This is the DSN for the MySQL server. Use 'mysql:host=mysql.company.com' if your database server is mysql.company.com.
   - $databaseUsername - This is the username for the MySQL server.
   - $databasePasswd - This is the password for the MySQL server.
2. Upload the api directory to your webserver.
3. Create a new database on the MySQL server. It needs to be in the format "Company_NNNNNN" where NNNNNN is a 6 digit number that does not start on a "0".
4. Run the SQL file "template_base.sql" in the newly created "Company_NNNNNN" database to create the base tables.
5. Now the tables in the database need to be loaded with country specific data. We have made sample data for Sweden. If you want to use it execute the SQL file "template_country_SE.sql" in the database "Country_NNNNNN".
6. Use one of the client applications for Android or iOS to access the server. When logging in, the "Username" field is "user@NNNNNN" and leave the "Password" field blank. The "Server URL" field should point to your public api directory on your webserver.
7. After logging in, you will see a menu. Change the password of the account by tapping Administration > User database > Change > User > Change password
8. You are ready to start working with the application now.
9. Be sure to backup your database often!

License
-------
Copyright 2012-2017 Infium AB

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

<http://www.apache.org/licenses/LICENSE-2.0>

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
