<?php
/**
 * fakeAppConfiguration
 * @author Artem Chistyakov <chistyakov.artem@gmail.com>
 * Fake Application Configuration for testing needs.
 */
class fakeAppConfiguration extends sfApplicationConfiguration {
  public function configure() {}
}

class myUser extends sfBasicSecurityUser
{
}