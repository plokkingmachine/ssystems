# Installation Moodle Server Manually

## Installation and configuration of `fail2ban`

``` bash title="Install fail2ban and copy configuration file"
sudo apt install fail2ban
sudo cp /etc/fail2ban/jail.conf /etc/fail2ban/jail.local
```

``` bash title="SSHD entry from original configuration file"
[sshd]
#
enabled  = true
port     = ssh
filter   = sshd
logpath  = %(sshd_log)s
backend  = %(sshd_backend)s
maxretry = 2
findtime = 10m
bantime  = 12h
```

``` bash title="Restart and enable fail2ban service"
sudo systemctl restart fail2ban[.service]
sudo systemctl enable fail2ban[.service]
sudo systemctl status fail2ban.service
```

``` bash title="Status for fail2ban sshd"
sudo fail2ban-client status sshd
```
``` output
Status for the jail: sshd
|- Filter
|  |- Currently failed: 1
|  |- Total failed:     3
|  `- Journal matches:  _SYSTEMD_UNIT=ssh.service + _COMM=sshd
`- Actions
   |- Currently banned: 1
   |- Total banned:     1
   `- Banned IP list:   185.156.73.233
```


## Basic firewall with `ufw`

``` bash
sudo apt install ufw
```

``` bash
sudo su -s
ufw default deny incoming
ufw default allow outgoing
ufw limit 22/tcp
ufw allow 80/tcp
ufw allow 443/tcp
ufw enable
ufw status verbose
iptables -L
```



## Installing Moodle manually

### Installing `postgresql` and `nginx`

``` bash title="Installing packages"
sudo apt install postgresql nginx
```

### `nginx` basic server configuration to issue certificates

``` nginx title="nginx server basic configuration"
server {
        listen 80;
        listen [::]:80;
        server_name ssystems.XXXXXXXXXXXX.de www.ssystems.XXXXXXXXXXXX.de;

        location /.well-known/acme-challenge/ {
                root /var/www/le_root;
                allow all;
                access_log off;
        }

        # # Only root path 301 to allow acme challenge
        # location / {
        #         return 301 https://www.ssystems.XXXXXXXXXXXX.de$request_uri;
        # }
}

# # 301 for URL without www. prefix
# server {
#         listen 443 ssl;
#         listen [::]:443 ssl;
#         http2 on;
#         server_name ssystems.XXXXXXXXXXXX de;
# 
#         ssl_certificate     /etc/nginx/certs/ssystems.XXXXXXXXXXXX.de/fullchain.pem;
#         ssl_certificate_key /etc/nginx/certs/ssystems.XXXXXXXXXXXX.de/privkey.pem;
# 
#         return 301 https://www.ssystems.XXXXXXXXXXXX.de$request_uri;
# }
# 
# # Actual configuration for Moodle
# server {
#         listen 443 ssl;
#         listen [::]:443 ssl;
#         http2 on;
#         server_name www.ssystems.XXXXXXXXXXXX.de;
# 
#         ssl_certificate     /etc/nginx/certs/ssystems.XXXXXXXXXXXX.de/fullchain.pem;
#         ssl_certificate_key /etc/nginx/certs/ssystems.XXXXXXXXXXXX.de/privkey.pem;
# 
#         # MOODLE FAST CGI PASS PLACEHOLDER
# }
```


### Installing and configuring `acme.sh`

Create extra user acme (limited permissions, separate crontab)

``` bash 
sudo useradd -g users -G sudo -s /bin/bash -m acme
sudo passwd acme
```
Let me change to user acme without passwd: `sudo visudo /etc/sudoers`

``` bash 
frederik ALL=(acme) NOPASSWD: ALL
```
Test user switching

``` bash 
sudo -u acme -i
```

[https://github.com/acmesh-official/acme.sh/wiki/How-to-install](https://github.com/acmesh-official/acme.sh/wiki/How-to-install)

Install `acme.sh`

``` bash 
sudo apt update
sudo apt install ca-certificates
curl https://get.acme.sh | sh
```

Enable `acme` user to run passwordless `sudo acme.sh` in `/etc/sudoers`
``` bash 
acme ALL=(ALL:ALL) NOPASSWD: /home/acme/.acme.sh/acme.sh *, /etc/init.d/nginx force-reload
```

Create directories for ACME challenge

``` bash 
sudo mkdir /var/www/le_root
sudo chown -R acme:www-data /var/www/le_root
sudo chmod -R 2755 /var/www/le_root
sudo -u acme mkdir -p .well-known/acme-challenge
```

Issue the certificate

``` bash 
./.acme.sh/acme.sh --issue -d 'www.ssystems.XXXXXXXXXXXX.de' -d 'ssystems.XXXXXXXXXXXX.de' -w /var/www/le_root --server letsencrypt
```

Create directories for installed certificates `acme.sh` and `nginx` can access
``` bash 
sudo mkdir /etc/nginx/certs
sudo chown acme:www-data /etc/nginx/certs
sudo chmod 2755 /etc/nginx/certs
mkdir /etc/nginx/certs/ssystems.XXXXXXXXXXXX.de
```

Install the certificate

``` bash 
./.acme.sh/acme.sh --install-cert -d 'www.ssystems.XXXXXXXXXXXX.de' -d 'ssystems.XXXXXXXXXXXX.de' --key-file /etc/nginx/certs/ssystems.XXXXXXXXXXXX.de/privkey.pem --fullchain-file /etc/nginx/certs/ssystems.XXXXXXXXXXXX.de/fullchain.pem --reloadcmd "sudo /etc/init.d/nginx force-reload"
```









### Download and copy files


[https://docs.moodle.org/501/en/Installing_Moodle#Download_and_copy_files_into_place](https://docs.moodle.org/501/en/Installing_Moodle#Download_and_copy_files_into_place)

Clone repository and set branch to MOODLE_501_STABLE

``` bash 
sudo apt install git
sudo su -
git clone -b MOODLE_501_STABLE git://git.moodle.org/moodle.git lms /var/www/html/lms
chown -R root /var/www/html/lms/
chmod -R 0755 /var/www/html/lms/
```






### Create an empty database

[https://docs.moodle.org/501/en/PostgreSQL#Creating_Moodle_Database](https://docs.moodle.org/501/en/PostgreSQL#Creating_Moodle_Database)

``` bash
# Switch to postgres user
sudo -u postgres -i
psql
```

``` postgres title="Create a new user for Moodle"
CREATE USER moodleuser WITH PASSWORD 'XXXXXXXXXXXXXXXXXXXXXXX';
```
``` postgres title="Create the database"
CREATE DATABASE moodle WITH OWNER moodleuser;
```

List databases and quit if correct

``` postgres
\l
```
``` output
                                                      List of databases
   Name    |   Owner    | Encoding | Locale Provider |   Collate   |    Ctype    | Locale | ICU Rules |   Access privileges
-----------+------------+----------+-----------------+-------------+-------------+--------+-----------+-----------------------
 moodle    | moodleuser | UTF8     | libc            | en_US.UTF-8 | en_US.UTF-8 |        |           |
 postgres  | postgres   | UTF8     | libc            | en_US.UTF-8 | en_US.UTF-8 |        |           |
 template0 | postgres   | UTF8     | libc            | en_US.UTF-8 | en_US.UTF-8 |        |           | =c/postgres          +
           |            |          |                 |             |             |        |           | postgres=CTc/postgres
 template1 | postgres   | UTF8     | libc            | en_US.UTF-8 | en_US.UTF-8 |        |           | =c/postgres          +
           |            |          |                 |             |             |        |           | postgres=CTc/postgres
(4 rows)
```


Edit the client authentication file: `/etc/postgresql/17/main/pg_hba.conf`

``` bash
# MOODLE
host  moodle    moodleuser    127.0.0.1/32    password
```

Restart PostgreSQL service
``` bash
/etc/init.d/postgresql restart
```





### Create the (moodledata) data directory

[https://docs.moodle.org/501/en/Installing_Moodle#Create_the_(moodledata)_data_directory](https://docs.moodle.org/501/en/Installing_Moodle#Create_the_(moodledata)_data_directory)

Since I am not using a shared server but a server with Moodle as standalone service, the documentation allows no restrictions:

``` title="Create very permissive directory"
mkdir -p /var/moodledata
chmod 0777 /var/moodledata
```





### INSTALL PHP8.4 and PHP necessary modules

[https://docs.moodle.org/501/en/PHP#PHP_Versions](https://docs.moodle.org/501/en/PHP#PHP_Versions)

To find the correct package names I used GLM4.6 (Kagi):

| PHP Module | APT Package Name |
| --- | --- |
| ctype | Included in php-common |
| curl  | php-curl |
| dom |php-xml                   |
| gd |php-gd                      |
| iconv |Included in php-common               |
| intl |php-intl                  |
| json |php-json                  |
| mbstring |php-mbstring          |
| pcre |Included in php-common    |
| simplexml |php-xml             |
| spl |Included in php-common    |
| xml |php-xml                   |
| zip |php-zip                   |
| openssl | Included in php-common |
| soap | php-soap |
| sodium | php-sodium |
| tokenizer | php-tokenizer |
| xmlrpc | php-xmlrpc |


Install PHP8.4
``` bash 
apt install php php-pgsql php-common php-curl php-xml php-gd php-intl php-json php-mbstring php-zip php-soap php-tokenizer php-xmlrpc php-fpm
```

Find the correct configuration file
``` bash 
php -i | grep php.ini
```
``` output
Configuration File (php.ini) Path => /etc/php/8.4/cli
Loaded Configuration File => /etc/php/8.4/cli/php.ini
```

`/etc/php/8.4/cli/php.ini`
``` ini 
memory_limit = -1
session.save_handler = files
magic_quotes_runtime (DEPRECATED)
file_uploads = On
session.auto_start = 0
TEMP FOLDER WAS CONFIGURED EXTERNALLY
ERRORS AS DEFAULT
post_max_size = 201M
upload_max_filesize = 200M
max_input_vars = 10000 (at least 5000, otherwise installation doesn't finish)
```







### Start Moodle Install with Command Line Installer

``` bash
chown www-data /var/www/html/lms
cd /var/www/html/lms/admin/cli/
sudo -u www-data /usr/bin/php install.php
```
``` output
root@fw-ssystems:/var/www/html/lms/admin/cli# sudo -u www-data /usr/bin/php install.php
                                 .-..-.
   _____                         | || |
  /____/-.---_  .---.  .---.  .-.| || | .---.
  | |  _   _  |/  _  \/  _  \/  _  || |/  __ \
  * | | | | | || |_| || |_| || |_| || || |___/
    |_| |_| |_|\_____/\_____/\_____||_|\_____)

Moodle 5.1+ (Build: 20251017) command line installation program
-------------------------------------------------------------------------------
== Choose a language ==
en - English (en)
? - Available language packs
type value, press Enter to use default value (en)
:
```


``` output
== Copyright notice ==
Moodle  - Modular Object-Oriented Dynamic Learning Environment
Copyright (C) 1999 onwards Martin Dougiamas (https://moodle.com)

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.

See the Moodle License information page for full details: https://moodledev.io/general/license

Have you read these conditions and understood them?
type y (means yes) or n (means no)
: y
...
...
...
...
Installation completed successfully.
```





### PHP-FPM security regarding file extensions

`/etc/php/8.4/fpm/pool.d/www.conf`
``` conf 
security.limit_extensions = .php
```







### Complete Nginx configuration


``` nginx
server {
        listen 80;
        listen [::]:80;
        server_name ssystems.XXXXXXXXXXXX.de www.ssystems.XXXXXXXXXXXX.de;

        location /.well-known/acme-challenge/ {
                root /var/www/le_root;
                allow all;
                access_log off;
        }

        location / {
                return 301 https://www.ssystems.XXXXXXXXXXXX.de$request_uri;
        }
}

server {
        listen 443 ssl;
        listen [::]:443 ssl;
        http2 on;
        server_name ssystems.XXXXXXXXXXXX.de;

        ssl_certificate     /etc/nginx/certs/ssystems.XXXXXXXXXXXX.de/fullchain.pem;
        ssl_certificate_key /etc/nginx/certs/ssystems.XXXXXXXXXXXX.de/privkey.pem;

        return 301 https://www.ssystems.XXXXXXXXXXXX.de$request_uri;
}

server {
        listen 443 ssl;
        listen [::]:443 ssl;
        http2 on;
        server_name www.ssystems.XXXXXXXXXXXX.de;

        ssl_certificate     /etc/nginx/certs/ssystems.XXXXXXXXXXXX.de/fullchain.pem;
        ssl_certificate_key /etc/nginx/certs/ssystems.XXXXXXXXXXXX.de/privkey.pem;

        root /var/www/html/;
        index index.php index.html index.htm;

        client_max_body_size 128M;
        client_body_timeout 300s;
        client_header_timeout 300s;

        location / {
                return 301 /lms/;
        }

        # https://docs.moodle.org/501/en/Nginx#Routing_Engine
        #
        # My Moodle site is hosted in a sub-directory (/lms/) so I specify it here
        #
        location /lms {
                return 301 /lms/;
        }

        # This is responsible for rewriting the path for
        # .php files living in
        #   /lms/lib/
        #   /lms/theme/
        #   /lms/login/
        #   /lms/admin/
        #   /lms/my/
        #   /lms/profile/
        location ~ ^/lms/(lib|theme|login|admin|my|profile)/.*\.php.*$ {
                rewrite ^/lms/(lib|theme|login|admin|my|profile)/(.*)$ /lms/public/$1/$2 last;
        }

        # First approach for public/ rewrites
        #
        #location ~ ^/lms/(my|extra)/(.*)$ {
        #       rewrite ^/lms/(my|extra)/(.*)$ /lms/public/$1/$2 last;
        #}

        # Second approach
        #
        #  Script which lists directories in public/ and concatenates basenames to
        #  build rule automatically (see below)
        #
        location ~ ^/lms/(admin|ai|analytics|auth|availability|backup|badges|blocks|blog|cache|calendar|cohort|comment|communication|competency|completion|contentbank|course|customfield|dataformat|enrol|error|favourites|files|filter|grade|group|h5p|install|iplookup|lang|lib|local|login|media|message|mnet|mod|moodlenet|my|notes|payment|pix|plagiarism|portfolio|privacy|question|rating|report|reportbuilder|repository|rss|search|sms|tag|theme|user|userpix|webservice)/(.*)$ {
                rewrite ^/lms/(admin|ai|analytics|auth|availability|backup|badges|blocks|blog|cache|calendar|cohort|comment|communication|competency|completion|contentbank|course|customfield|dataformat|enrol|error|favourites|files|filter|grade|group|h5p|install|iplookup|lang|lib|local|login|media|message|mnet|mod|moodlenet|my|notes|payment|pix|plagiarism|portfolio|privacy|question|rating|report|reportbuilder|repository|rss|search|sms|tag|theme|user|userpix|webservice)/(.*)$ /lms/public/$1/$2 last;
        }

        # This block is used to avoid non-properly redirections and
        # loops (?). I must admit I do not fully understand the looping.
        #
        location /lms/ {
                # Base rewrite for clean URLs
                try_files $uri $uri/ /lms/r.php;
        }

        location /dataroot/ {
                internal;
                alias /var/moodledata/;
        }

        #location ~ (/vendor/|/node_modules/|composer\.json|/readme|/README|readme\.txt|/upgrade\.txt|/UPGRADING\.md|db/install\.xml|/fixtures/|/behat/|phpunit\.xml|\.lock|environment\.xml) {
        #       deny all;
        #       return 404;
        #}

        # https://docs.moodle.org/501/en/Nginx#FastCGI_Configuration
        #
        # I took this mainly from the documentation and adapted it to my unix
        # socket listening ready to interpret
        #
        # Pass to PHP Interpreter
        #
        #location ~ \.php(/|$) {
        location ~ [^/].php(/|$) {
                # Split the path info based on URI.
                fastcgi_split_path_info ^(.+\.php)(/.*)$;
                # Note: Store the original path_info. It will be wiped out in a moment by try_files.
                set $path_info $fastcgi_path_info;

                # Look for the php file, trying a trailing slash for directories if required.
                try_files $fastcgi_script_name $fastcgi_script_name/;

                # File was found -- pass to fastcgi.
                fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
                include fastcgi_params;

                # Re-apply the path_info after including fastcgi_params.
                fastcgi_param PATH_INFO $path_info;
                #                             $document_root$fastcgi_script_name
                fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
                fastcgi_param DOCUMENT_ROOT $realpath_root;
        }

        # Logging
        access_log /var/log/nginx/moodle_access.log;
        error_log /var/log/nginx/moodle_error.log;
}
```


Bash script to create location block for nginx config
``` bash 
#!/bin/bash

if [ ! -d "$1" ]; then
    echo "Error: Directory '$1' does not exist."
    exit 1
fi

# List all subdirectories in public/ and extract their names
# Use ls -d to list directories
# basename to isolate the names from path
# xargs -n1 limits execution to one element at a time
DIRS=$(ls -d "$1"/*/ 2>/dev/null | xargs -n1 basename)

# Join directories
ALT_DIRS=$(echo "$DIRS" | tr '\n' '|' | sed 's/|$//')

# Generate Nginx block
echo "location ~ ^/lms/($ALT_DIRS)/(.*)$ {"
echo "    rewrite ^/lms/($ALT_DIRS)/(.*)$ /lms/public/\$1/\$2 last;"
echo "}"
```

The admin passwd for Moodle is placed in `/root/moodle_adminpasswd`














# Developing a new Activity/module

Module code: [/mod_apifetchpower/](/mod_apifetchpower/)

[https://moodledev.io/docs/5.1/apis/plugintypes/mod](https://moodledev.io/docs/5.1/apis/plugintypes/mod) gives information about which files are needed for a module to work.
The source code and file structure of other modules is very helpful:

```
[apifetchpower]
assign
bigbluebuttonbn
book
choice
data
feedback
folder
forum
glossary
h5pactivity
imscp
label
lesson
lti
page
qbank
quiz
resource
scorm
subsection
url
wiki
workshop
```
Since I created a plugin in `/local` at first, I copied my working code into a module and adapted the structure and mandatory files to the one demanded for modules.

I explicitly avoided any database calls since there is nothing relevant to store for the API calls.

The error messages in the browser regarding missing `$string`s were helpful and directing.












# Installation Moodle Server with Salt 

[https://docs.saltproject.io/salt/install-guide/en/latest/topics/install-by-operating-system/linux-deb.html](https://docs.saltproject.io/salt/install-guide/en/latest/topics/install-by-operating-system/linux-deb.html)


There is an open issue for almost 2 years regarding file.managed: [\[BUG\] 3006.5 salt-ssh "Unable to manage file: none of the specified sources were found" #65882](https://github.com/saltstack/salt/issues/65882)

[\[BUG\] salt-ssh from newer Python (3.9) to 3.6.x host fails #61419](https://github.com/saltstack/salt/issues/61419)

[\[BUG\] file.managed failed via salt-ssh, having python >=3.12 + cffi being installed #68080](https://github.com/saltstack/salt/issues/68080)

[salt-ssh not working on remote hosts with python versions >3.8 #61276](https://github.com/saltstack/salt/issues/61276)

## venv

Creating a virtual environment with python 3.9 and salt 3006.2 to avoid conflicts with file.managed

``` bash
uv init saltenv3.9
cd saltenv3.9
echo "3.9" > .python-version
vim pyproject.toml # requires-python --> 3.9
uv venv --python 3.9
uv python pin 3.9
source .venv/bin/activate
uv add distro jinja2 looseversion msgpack packaging pyyaml salt==3006.2
```

``` bash
(saltenv3.9) ubuntu@ubuntu2404:~/saltenv3.9$ python --version
Python 3.9.24
(saltenv3.9) ubuntu@ubuntu2404:~/saltenv3.9$ salt --version
salt 3006.5 (Sulfur)
```

Roster file

``` yaml
ssystems:
  host: 1XX.XXX.XXX.XXX
  user: frederik
  sudo: true
  python3: /home/frederik/.pyenv/versions/3.8.19/bin/python3
```

Place SSH-Key

``` bash
sudo ssh-copy-id -i /etc/salt/pki/master/ssh/salt-ssh.rsa.pub frederik@ssystems.XXXXXXXXXXXX.de
```

**Give user sudo NOPASSWD: permissions!**

*Optional: Install pyenv for a pinned python version execution environment*


Testing connection
``` bash
sudo salt-ssh 'ssystems' test.ping
```

Test if files in `/srv/salt/` are readable
``` bash
sudo salt-run fileserver.file_list
```
```
- moodle/files/apifetchpower/lang/en/local_apifetchpower.php
- moodle/files/apifetchpower/lib.php
- moodle/files/apifetchpower/styles.css
- moodle/files/apifetchpower/uplot.min.js
- moodle/files/apifetchpower/version.php
- moodle/files/apifetchpower/view.php
- moodle/files/files.tar.gz
- moodle/files/jail.local
- moodle/files/php.ini
- moodle/files/ssystems.XXXXXXXXXXXX.de
- moodle/files/ssystems.XXXXXXXXXXXX.de_certs/fullchain.pem
- moodle/files/ssystems.XXXXXXXXXXXX.de_certs/privkey.pem
- moodle/init.sls
- testfile.txt
- top.sls
```


``` 
```







## Bash-Script

Since I couldn't get `file.managed` to work properly, I created a `files.tar.gz` containing all the relevant configuration files and certificates.

`run.sh`

``` bash
#!/bin/bash

scp ~/work/ssystems/salt-ssh/files/files.tar.gz fwssystems:~

sudo salt-ssh 'ssystems' state.apply moodle
```





## `.sls`

```
update_repos:
  cmd.run:
    - name: apt update

upgrade_packages:
  cmd.run:
    - name: apt upgrade -y
    - require:
      - cmd: update_repos

install_packages:
  pkg.installed:
    - names:
      - nginx
      - postgresql
      - git
      - fail2ban
      - ufw
      - ca-certificates
      - php
      - php-pgsql
      - php-common
      - php-curl
      - php-xml
      - php-gd
      - php-intl
      - php-json
      - php-mbstring
      - php-zip
      - php-soap
      - php-tokenizer
      - php-xmlrpc
      - php-fpm

extract_files:
  cmd.run:
    - name: |
        tar -xzvf /home/frederik/files.tar.gz && \
        chown -R $USER:$USER apifetchpower/ jail.local php.ini ssystems.XXXXXXXXXXXX.de ssystems.XXXXXXXXXXXX.de

replace_php_ini:
  cmd.run:
    - name: |
        mv ~/php.ini /etc/php/8.4/cli/

adapt_fpmpool_config:
  cmd.run:
    - name: |
        sudo echo "security.limit_extensions = .php" >> /etc/php/8.4/fpm/pool.d/www.conf

# Approaches to utilize file.managed and workarounds:
#replace_php_ini:
  # Since I have python 3.12.3 on my machine:
  # https://github.com/saltstack/salt/issues/68080
  # --> uv venv
  # uv init saltenv
  # cd saltenv
  # uv venv --python 3.9
  # source .venv/bin/activate
  # uv add salt==3006.2 looseversion packaging distro PyYaml jinja2 msgpack
  # sudo salt-run fileserver.clear_cache && sudo salt-run fileserver.update
  #
  # ----------
  #           ID: replace_php_ini
  #     Function: file.copy
  #         Name: /etc/php/8.4/cli/php.ini
  #       Result: False
  #      Comment: Source file "/srv/salt/moodle/files/php.ini" is not present
  #      Started: 09:16:40.462841
  #     Duration: 0.653 ms
  #      Changes:
  #
  # Summary for ssystems
  # -------------
  # Succeeded: 22 (changed=2)
  # Failed:     1
  # -------------
  # Total states run:     23
  # Total run time:    2.810 s
  # [DEBUG   ] Using selector: EpollSelector
  # [DEBUG   ] Using selector: EpollSelector
  # [DEBUG   ] Using selector: EpollSelector
  # [DEBUG   ] Publisher connecting to /var/run/salt/master/master_event_pull.ipc
  # [DEBUG   ] Closing _TCPPubServerPublisher instance
  # (saltenv) ubuntu@ubuntu2404:~/saltenv$ sudo ls -l /srv/salt/moodle/files/php.ini
  # -rw-r--r-- 1 ubuntu users 69369 Oct 24 07:51 /srv/salt/moodle/files/php.ini
  # (saltenv) ubuntu@ubuntu2404:~/saltenv$ ls -l /srv/salt/moodle/files/php.ini
  # -rw-r--r-- 1 ubuntu users 69369 Oct 24 07:51 /srv/salt/moodle/files/php.ini
  # (saltenv) ubuntu@ubuntu2404:~/saltenv$ salt --version
  # salt 3006.5 (Sulfur)
  # (saltenv) ubuntu@ubuntu2404:~/saltenv$ python3 --version
  # Python 3.9.24
  #
  # file.copy:
  #   - name: /etc/php/8.4/cli/php.ini
  #   - source: /srv/salt/moodle/files/php.ini
  #   - force: true
  #
  # Also does NOT work!
  #module.run:
  #  - name: cp.get_file
  #  - path: salt://moodle/files/php.ini
  #  - dest: /etc/php/8.4/cli/php.ini
  #  - makedirs: true
  #
  # Also does NOT work
  # cmd.run:
  #   - name: |
  #       ssh {{ grains['id'] }} "sudo mkdir -p /etc/php/8.4/cli && \
  #       cat > /etc/php/8.4/cli/php.ini && \
  #       chmod 644 /etc/php/8.4/cli/php.ini" < /srv/salt/moodle/files/php.ini
  #   - retries: 3
  #   - retry_delay: 1
  #
  # Also does not work
  #cmd.script:
  #  - source: /srv/salt/moodle/files/php.ini
  #  - name: |
  #      mkdir -p /etc/php/8.4/cli && \
  #      cat > /etc/php/8.4/cli/php.ini && \
  #      chmod 644 /etc/php/8.4/cli/php.ini
  #  - shell: bash
  #
  #cmd.run:
  #  - name: |
  #      scp /srv/salt/moodle/files/php.ini {{ grains['id'] }}:/tmp/php.ini && \
  #      ssh {{ grains['id'] }} "sudo mkdir -p /etc/php/8.4/cli && \
  #      sudo mv /tmp/php.ini /etc/php/8.4/cli/php.ini && \
  #      sudo chmod 644 /etc/php/8.4/cli/php.ini"
  #  - retries: 1
  #
  # FIX in url.py
  # https://github.com/saltstack/salt/issues/68080#issuecomment-3026951670
  #
  # file.copy:
  #   - name: /etc/php/8.4/cli/php.ini
  #   - source: /src/salt/moodle/files/php.ini
  #   - force: True
  #
  #
  #file.managed:
  #  - name: /etc/php/8.4/cli/php.ini
  #  - source: /srv/salt/moodle/files/php.ini
  #  - saltenv: base
  #  - user: root
  #  - makedirs: true
  #  - replace: true
  #
  #file.managed:
  #  - name: /etc/php/8.4/cli/testfile.txt
  #  #- source: /srv/salt/testfile.txt
  #  - source: salt://testfile.txt
  #  - makedirs: true

move_jail_local:
  cmd.run:
    - name: |
        mv ~/jail.local /etc/fail2ban/

move_moodle_nginx_conf:
  cmd.run:
    - name: |
        mv ~/ssystems.XXXXXXXXXXXX.de /etc/nginx/conf.d/ssystems.XXXXXXXXXXXX.de.conf

postgres_database_init:
  cmd.run:
    - name: |
        sudo -u postgres psql -c "CREATE USER moodleuser WITH PASSWORD 'XXXXXXXXXXXXXXX';"
        sudo -u postgres psql -c "CREATE DATABASE moodle WITH OWNER moodleuser;"
        echo "host  moodle    moodleuser    127.0.0.1/32    password" >> /etc/postgresql/17/main/pg_hba.conf && \
        /etc/init.d/postgresql restart

gitclone_moodle:
  cmd.run:
    - name: |
        git clone -b MOODLE_501_STABLE git://git.moodle.org/moodle.git /var/www/html/lms && \
        sudo chown -R www-data:www-data /var/www/html/lms/ && \
        sudo chmod -R 0755 /var/www/html/lms/ && \
        sudo find /var/www/html/lms/ -type f -exec chmod 644 {} \;

create_moodledata_directory:
  cmd.run:
    - name: |
        sudo mkdir -p /var/moodledata && \
        chmod 0777 /var/moodledata

move_moodle_module:
  cmd.run:
    - name: |
        mv -f ~/apifetchpower /var/www/html/lms/public/mod/

install_certificates:
  cmd.run:
    - name: |
        mkdir -p /etc/nginx/certs && \
        mv -f ~/ssystems.XXXXXXXXXXXX.de_certs /etc/nginx/certs/ssystems.XXXXXXXXXXXX.de

nginx_enable:
  service.enabled:
    - name: nginx
    - enable: true

nginx_restart:
  service.running:
    - name: nginx
    - restarted: true

php_fpm_enable:
  service.enabled:
    - name: php8.4-fpm
    - enable: true

php_fpm_restart:
  service.running:
    - name: php8.4-fpm
    - restarted: true

postgresql_enable:
  service.enabled:
    - name: postgresql
    - enable: true

postgresql_restart:
  service.running:
    - name: postgresql
    - restarted: true
```














# Appendix 

## Extract public keys

The e-mail is signed with several certificates.<br>
The Public SSH Keys can be derived from certificates which look like the following:

``` output
-----BEGIN CERTIFICATE-----
MIIGPzCCBKegAwIBAgIQOtkJAnT/8r8q8Z0kKqxSijANBgkqhkiG9w0BAQsFADBY
MQswCQYDVQQGEwJHQjEYMBYGA1UEChMPU2VjdGlnbyBMaW1pdGVkMS8wLQYDVQQD
EyZTZWN0aWdvIFB1YmxpYyBFbWFpbCBQcm90ZWN0aW9uIENBIFIzNjAeFw0yNTA5
MDMwMDAwMDBaFw0yNzA5MDMyMzU5NTlaMCQxIjAgBgkqhkiG9w0BCQEWE3N1cHBv
cnRAc3N5c3RlbXMuZGUwggIiMA0GCSqGSIb3DQEBAQUAA4ICDwAwggIKAoICAQDA
coijx8xY7OKzLxk9H9FlB2wcX6otYOttaYFsfOUWA+qqT4oWRlotgmNaMeX3S6U2
m8xd7s/VSlrwQ7HCnt7wHAdVCLHm+jETvNzRz9s1apgGPGcOTiq+FO14T29HDzzn
Q5EP/Gh6ymnKf3hKhfjpJ/qtDmQjWlE8Sf8jRtarNuGzP3kM5UnuQmVJ4kOzhzR/
hFWBnVU7MB20aJWZW81g529VbX6vbuBXru4mwanOjIpljmV5xG192U5XUaiNhJ92
5per3hd+di6wcTl6Y0wgdMwwmtgTvGmnvXX06tt0MDFlRWZUhusPLWOcEiD87LvX
3/MqyZWhUhJhJCmNnI1FdB71lJsgwtcxjMQYgcipJm2yJpDrAO+O5p2mFthpIrLO
sE3/Ofn+DLEJZUWzDDmLOFfNvLLNV0kwLzeQmxswK6b5pP/2cZ/Nn7okC5iu1eAA
Evq+CpLA06XqTo8WCJFvVN0b4u54AQ35JMZuX6oeuUXGdSg8ByjlSTi4pLlTGc7f
L65VueldBnHC31L8TB/RIoS0/5y/IlBmaqEHWKmlxHGqGenAAB0rxfhIOWBNQeY7
+jme8uDOkrDCpERUNAGIeeNMfOzs1AnCLofdvwGuLdCH/dOkRCDiJ4yhzpj5uyMN
lSkhe0OhS7KWaqkGkd7JV/C/NLa+fr6G4WYuemqYzQIDAQABo4IBtzCCAbMwHwYD
VR0jBBgwFoAUHwSkfkz2MrEJ4pF84WzfUr/PLv0wHQYDVR0OBBYEFEuFFsj82dO7
SVyHuPr/VzSGttbfMA4GA1UdDwEB/wQEAwIFoDAMBgNVHRMBAf8EAjAAMBMGA1Ud
JQQMMAoGCCsGAQUFBwMEMFAGA1UdIARJMEcwOgYMKwYBBAGyMQECAQoCMCowKAYI
KwYBBQUHAgEWHGh0dHBzOi8vc2VjdGlnby5jb20vU01JTUVDUFMwCQYHZ4EMAQUB
AzBNBgNVHR8ERjBEMEKgQKA+hjxodHRwOi8vY3JsLnNlY3RpZ28uY29tL1NlY3Rp
Z29QdWJsaWNFbWFpbFByb3RlY3Rpb25DQVIzNi5jcmwwfQYIKwYBBQUHAQEEcTBv
MEgGCCsGAQUFBzAChjxodHRwOi8vY3J0LnNlY3RpZ28uY29tL1NlY3RpZ29QdWJs
aWNFbWFpbFByb3RlY3Rpb25DQVIzNi5jcnQwIwYIKwYBBQUHMAGGF2h0dHA6Ly9v
Y3NwLnNlY3RpZ28uY29tMB4GA1UdEQQXMBWBE3N1cHBvcnRAc3N5c3RlbXMuZGUw
DQYJKoZIhvcNAQELBQADggGBAINKU2NvjulSQeAR7LntIlDKqOhXhk1uTgziZ/En
mL8P5BtMFV0czq+lDuIUp2qsnBOtKqjvg3sJPQLfOk8xLhnTU29DHPCC8PWv4Gc1
9VU1+mRYw2m7wq022Nx9Yi/D13AgEtGh2HDGDWihBVYCfR4/5ceY7OLWE0zD/CQs
Fhqv4uRtz1h1q7z0+tXq3u4UXhFoiILVB3Qss7DJL3Xj9GLL/rcuYbP9f/tGtIPh
ekRN3xokNs74g4e/oqu9YIWZTRC46ud6QijMPzvD1QN4MotCeOUzSZKgjxwKuaCP
8+qnbjQaK7jI6YTnPZuxPs19UEQmcYWsS5Jp7kHvLdjwZFOZoRr/KMgignI+aaBn
YJEJHgHDFyht794x7HEgmg5p3giVvDn/YJESYAzuuIdA5PcbnBQGgBKLr2H8VRIO
yxswF4lTY1dHvh6O0Xl9kYAJAesmLbI/hUbUJaXn3jwtF80/4MxZ9jlYDhYJ1yMn
s+zMm7qORKwIbd96vt713GBaMg==
-----END CERTIFICATE-----
```


Extracting and converting a single certificate to public ssh key

``` bash
cat cert.pem | openssl x509 -pubkey -noout | ssh-keygen -i -m PKCS8 -f /dev/stdin > id_rsa.pub
```

```
-----BEGIN PUBLIC KEY-----
MIICIjANBgkqhkiG9w0BAQEFAAOCAg8AMIICCgKCAgEAwHKIo8fMWOzisy8ZPR/R
ZQdsHF+qLWDrbWmBbHzlFgPqqk+KFkZaLYJjWjHl90ulNpvMXe7P1Upa8EOxwp7e
8BwHVQix5voxE7zc0c/bNWqYBjxnDk4qvhTteE9vRw8850ORD/xoesppyn94SoX4
6Sf6rQ5kI1pRPEn/I0bWqzbhsz95DOVJ7kJlSeJDs4c0f4RVgZ1VOzAdtGiVmVvN
YOdvVW1+r27gV67uJsGpzoyKZY5lecRtfdlOV1GojYSfduaXq94XfnYusHE5emNM
IHTMMJrYE7xpp7119OrbdDAxZUVmVIbrDy1jnBIg/Oy719/zKsmVoVISYSQpjZyN
RXQe9ZSbIMLXMYzEGIHIqSZtsiaQ6wDvjuadphbYaSKyzrBN/zn5/gyxCWVFsww5
izhXzbyyzVdJMC83kJsbMCum+aT/9nGfzZ+6JAuYrtXgABL6vgqSwNOl6k6PFgiR
b1TdG+LueAEN+STGbl+qHrlFxnUoPAco5Uk4uKS5UxnO3y+uVbnpXQZxwt9S/Ewf
0SKEtP+cvyJQZmqhB1ippcRxqhnpwAAdK8X4SDlgTUHmO/o5nvLgzpKwwqREVDQB
iHnjTHzs7NQJwi6H3b8Bri3Qh/3TpEQg4ieMoc6Y+bsjDZUpIXtDoUuylmqpBpHe
yVfwvzS2vn6+huFmLnpqmM0CAwEAAQ==
-----END PUBLIC KEY-----
```

```
ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAACAQDAcoijx8xY7OKzLxk9H9FlB2wcX6otYOttaYFsfOUWA+qqT4oWRlotgmNaMeX3S6U2m8xd7s/VSlrwQ7HCnt7wHAdVCLHm+jETvNzRz9s1apgGPGcOTiq+FO14T29HDzznQ5EP/Gh6ymnKf3hKhfjpJ/qtDmQjWlE8Sf8jRtarNuGzP3kM5UnuQmVJ4kOzhzR/hFWBnVU7MB20aJWZW81g529VbX6vbuBXru4mwanOjIpljmV5xG192U5XUaiNhJ925per3hd+di6wcTl6Y0wgdMwwmtgTvGmnvXX06tt0MDFlRWZUhusPLWOcEiD87LvX3/MqyZWhUhJhJCmNnI1FdB71lJsgwtcxjMQYgcipJm2yJpDrAO+O5p2mFthpIrLOsE3/Ofn+DLEJZUWzDDmLOFfNvLLNV0kwLzeQmxswK6b5pP/2cZ/Nn7okC5iu1eAAEvq+CpLA06XqTo8WCJFvVN0b4u54AQ35JMZuX6oeuUXGdSg8ByjlSTi4pLlTGc7fL65VueldBnHC31L8TB/RIoS0/5y/IlBmaqEHWKmlxHGqGenAAB0rxfhIOWBNQeY7+jme8uDOkrDCpERUNAGIeeNMfOzs1AnCLofdvwGuLdCH/dOkRCDiJ4yhzpj5uyMNlSkhe0OhS7KWaqkGkd7JV/C/NLa+fr6G4WYuemqYzQ==
```
