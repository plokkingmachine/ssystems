
ssystems Aufgabenstellung

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

## Installation of Salt 

[https://docs.saltproject.io/salt/install-guide/en/latest/topics/install-by-operating-system/linux-deb.html](https://docs.saltproject.io/salt/install-guide/en/latest/topics/install-by-operating-system/linux-deb.html)




# Appendix 

## Extract public keys

The e-mail is signed with several certificates.<br>
The Public SSH Keys can be derived from those certificates which look like the following:

``` output title="certificate_supportATssystems.de.pem"
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


``` bash title="Extracting and converting a single certificate to public ssh key"
cat cert.pem | openssl x509 -pubkey -noout | openssl rsa -pubin -outform PEM | ssh-keygen -i -m PKCS8 -f /dev/stdin > id_rsa.pub
```

