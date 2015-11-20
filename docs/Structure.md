# Structure

There are 3 folders where PHP would be mainly found `App`, `Job`, `Model`. Each folder separates code mainly for resuability and has a particular pupose respectively in the overall architecture. The following sample should reflect your structure if you successfully installed this framework.

![Request Response](https://github.com/Eve-PHP/Framework/blob/master/docs/folders.png)

The folders in action are best represented in the following diagram.

![Request Response](https://github.com/Eve-PHP/Framework/blob/master/docs/rnr.jpg)

In a typical MVC design, controllers determine the action, actions would have access to models, while models have access to the database. This kind of separation allows models to be reused for different apps. In practice however, because of the nature of changing business requirements, it is possible for model methods to be app specific which was not the intention of the original MVC design. Those who successfully kept the intention of models, stuffed business rules in the action classes, but the problem with this is other apps could not reuse it because actions are normally binded to very specific templates. 

This was one of the main reasons why we introduced a new layer for business rules called `Jobs`. Jobs are reusable components across many apps to access different kind of business rules which inturn accesses models to interact to the database. To learn more about Jobs please see: [Jobs and Delayed Process](https://github.com/Eve-PHP/Framework/blob/master/docs/Jobs.md). Using job queues, we can transform the process to be highly scalable as described by the following illustration.

![Request Response](https://github.com/Eve-PHP/Framework/blob/master/docs/delayed.jpg)

## Continuous Delivery

In a world full of bugs, quality control, task tracking and maintenance, we were told that 25 percent of a developer's time is building and 75 percent of their time is spent on maintenance. Today's corporate clients require a solution for change management in which continuous delivery can provide. Eve follows a typical continous delivery process which utilizes a repository like [GitHub](https://github.com) and a build server for testing and deploying like [Travis CI](http://travis-ci.org). The following explains how this flow works.

![Continuous Deploys](https://github.com/Eve-PHP/Framework/blob/master/docs/ci.jpg)

To execute this flow, we practice the [GitHub Flow](https://google.com/?q=Github+Flow) in which we:

 1. Protect the master from being deleted and directed pushed into. 
 2. Enforce developers to branch out features, general tasks, and hotfixes.
 3. Submit a pull request to master.
 4. Review code and accept/deny the pull request.
 5. Repeat.

Next we connect the repository to the build server by signing up for *Travis CI* and connecting our repository using the *Travis CI* admin. We also include a `.travis.yml` in the root folder of our repository. The following shows an example `.travis.yml` file.

```
language: php
php:
  - 5.6
services:
  - mysql
  - rabbitmq
before_install:
    - sudo apt-get update
install:
  - pear install pear/PHP_CodeSniffer
  - phpenv rehash
  - sudo rabbitmq-plugins enable rabbitmq_management
  - sudo rabbitmq-plugins enable rabbitmq_federation
  - sudo rabbitmq-plugins enable rabbitmq_federation_management
  - sudo service rabbitmq-server restart
sudo: required
before_script:
  - echo "Install and setup apache"
  - sudo apt-get update > /dev/null
  - sudo apt-get install -y --force-yes apache2 libapache2-mod-php5 php5-curl php5-intl php5-gd php5-idn php-pear php5-imagick php5-imap php5-mcrypt php5-memcache php5-ming php5-ps php5-pspell php5-recode php5-snmp php5-sqlite php5-tidy php5-xmlrpc php5-xsl
  - sudo a2enmod rewrite
  - sudo sed -i -e "s,/var/www,/home/travis/build/clark21/gae-test,g" /etc/apache2/sites-available/default
  - sudo sed -i -e "s,AllowOverride[ ]None,AllowOverride All,g" /etc/apache2/sites-available/default
  - sudo /etc/init.d/apache2 restart
  - cp settings/sample.config.php settings/config.php
  - cp settings/sample.test.php settings/test.php
  - composer install
script:
  - phpunit -dzend.enable_gc=0
after_success:
  - chmod 600 ./deploy.pem
  - ./deploy production
  - ./deploy queue
```

In the example above there are a few key points. The first is we want to include MySQL and *RabbitMQ*. Next we want to make sure Apache is updated. Next we run the actual tests by `phpunit`. And last we want to deploy the code to our live server. `chmod 600 ./deploy.pem` is a shell key custom made depending on your server provider. If you are not sure how to generate this or add this, you should contact your service provider or read up [here](https://google.com/?q=pem+ssh+deploy). We need this key inorder to deploy without using a password. Lastly we use [the deploy script](https://github.com/visionmedia/deploy) which we also should include in the root repository folder and also a configuration file called `deploy.conf` with the following contents.

```
[production]
key ./deploy.pem
user [production server root name]
host [production server ip address]
repo git@github.com:[github organization name]/[repository name].git
path [the path to where to deploy this code to]
ref origin/[the branch name]

[queue]
user [RabbitMQ server root name]
host [RabbitMQ server ip address]
repo git@github.com:[github organization name]/[repository name].git
path [the path to where to deploy this code to]
ref origin/[the branch name]
```

It's important to understand that this framework is not responsible for writing your test code (though the generators can create it for you). You should always review and write custom tests according to the exact features of your app. To learn how to write effective tests in Eve please refer to [Writing Tests](https://github.com/Eve-PHP/Framework/blob/master/docs/Tests.md). 

Though this framework can work with just about any kind of server architecture, it might be confusing where RabbitMQ and CDN could fit in the picture. The following describes an example of how an architecture utilizing this framework would look like. We have also successfully deployed and optimized Eve using this.

![Continuous Deploys](https://github.com/Eve-PHP/Framework/blob/master/docs/architecture.jpg)

For a better consult on these matters, please talk to your server provider or for a quick consult, you can contact us directly at [info@openovate.com](mailto:info@openovate.com). Please refer to this documentation :)