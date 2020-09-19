<?php

/********************************************************************
 * Version 2.0
 * Front Page Dialog for Amazon Seller Central
 * Copyright 2014 Purple Turtle Productions. All rights reserved.
 * license    GNU General Public License version 3 or later; see GPLv3.txt
 * By: Suzan 2019-03
 ********************************************************************/

class WalmartDlg extends PBaseFeedDialog
{

    function __construct()
    {
        parent::__construct();
        $this->service_name = 'Walmart';
        $this->service_name_long = 'Walmart';
        $this->doc_link = "";
    }

    function convert_option($option)
    {
        return strtolower(str_replace(" ", "_", $option));
    }
}

