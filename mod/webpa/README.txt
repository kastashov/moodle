WebPA activity module
======================

How to install

1. Unzip the module into mod/ folder so that its version.php resides in mod/webpa/version.php

2. Download Simple SSO (local_sso) plugin and unzip under local/ folder so that its version.php goes to local/sso/version.php

3. Install both plugins as usual.

4. Configure the plugin under Site administration -> Plugins -> Activity modules -> WebPA Assessment

5. Launch shell (command line) to the server, navigate to local/sso folder and execute 
php keygen > filename.txt
to generate SSO keys and insert them into the database.

6. Use the file filename.txt generated in step 5 to configure WebPA server.

7. Log in with username that exists in WebPA to add an instance of WebPA activity.