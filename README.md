- start with docker `docker run --name wxwork -v /path/to/wxwork:/var/www -d php:version`
- install libs
  ```
  export http_proxy=http://x.x.x.x:x
  export https_proxy=http://x.x.x.x:x
  composer i
  ```
- test sample `./vendor/bin/phpunit test/helper/wechatuser`