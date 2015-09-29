<?php //-->
/*
 * This file is part of the Type package of the Eden PHP Library.
 * (c) 2013-2014 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE
 * distributed with this package.
 */

class ApiModelAddressSeachTest extends PHPUnit_Framework_TestCase
{
    public function testGetPublicAddress() 
	{
        $rows = eve()
        	->model('address')
        	->search()
        	->process()
        	->getRows();
		
		foreach ($rows as $row) {
			$this->assertEquals(1, $row['address_public']);
		}
    }
	
    public function testGetPrivateAddress() 
	{
        $rows = eve()
        	->model('address')
        	->search()
        	->process(array( 
				'filter' => array( 
					'address_active' => 1, 
					'address_public' => 0 )))
        	->getRows();
		
		foreach ($rows as $row) {
			$this->assertEquals(0, $row['address_public']);
		}
    }
}