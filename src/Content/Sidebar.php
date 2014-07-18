<?php
/*
 * ToDo:
 *  * Widget based cache instead of 1 for the whole sidebar
 *
 */

namespace Cache\Content;

class Sidebar extends Base {
	use Traits\SidebarInvalidation;
}