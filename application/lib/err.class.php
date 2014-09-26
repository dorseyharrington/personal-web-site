<?php
class ERR {
	//// Standard error constants
	const DUPLICATE_ENTRY = 1062 ;	// MySQL owns this one

	const ERR_DB_NOT_INITIALIZED_DESC = 'Object is not initialized for this operation' ;

	const ERR_MEMBER_NOT_INITIALIZED_DESC = 'Object is not initialized for this operation' ;
	const ERR_MEMBER_NOT_FOUND_DESC = "The username or password is incorrect" ;
	const ERR_MEMBER_NOT_UPDATED_DESC = "We can't update a member with username '%s'" ;
	const ERR_MEMBER_NOT_CREATED_DESC = "We can't create a member with username '%s'" ;
	const ERR_MEMBER_MISSING_INFORMATION_DESC = "Member can't be created or updated without username, password, and email address" ;

	const ERR_GUEST_NOT_INITIALIZED_DESC = 'Object is not initialized for this operation' ;
	const ERR_GUEST_NOT_FOUND_DESC = "We can't locate a guest with the key value %d" ;
	const ERR_GUEST_NOT_UPDATED_DESC = "Can't update guest_map key value %d" ;
	const ERR_GUEST_NOT_SAVED_DESC = "Can't save new guest_map record" ;

	const ERR_ITEM_NOT_INITIALIZED_DESC = 'Object is not initialized for this operation' ;
	const ERR_ITEM_NOT_FOUND_DESC = "We can't locate an item with ID '%d'" ;
	const ERR_ITEM_NOT_CREATED = "We can't create a new item";

	const ERR_PROJECT_NOT_INITIALIZED_DESC = 'Object is not initialized for this operation' ;
	const ERR_PROJECT_NOT_FOUND_DESC = "We can't locate a project/item with ID '%d'" ;
	const ERR_PROJECT_NOT_UPDATED_DESC = "Can't update field '%s' with value '%s'";
	const ERR_PROJECT_NOT_REMOVED_DESC = "Can't remove project with ID '%d'";
	const ERR_PROJECT_SEARCH_ERROR_DESC = "Error in search syntax: '%s'";

	const ERR_IMAGE_NOT_INITIALIZED_DESC = 'Object is not initialized for this operation' ;
	const ERR_IMAGE_NOT_FOUND_DESC = "We can't locate an image with ID '%d'" ;
	const ERR_IMAGE_NOT_UPDATED_DESC = "We can't update the image with ID '%d'";
	const ERR_IMAGE_NOT_REMOVED_DESC = "Error removing image with ID '%d'";
	const ERR_IMAGE_FILE_NOT_REMOVED_DESC = "Error removing file '%s'";
	const ERR_IMAGE_FILE_NOT_UPLOADED_DESC = "Error uploading image file name %s";
	const ERR_IMAGE_NOT_VALID_DESC = "We can't process the image file %s";
	const ERR_IMAGE_TOO_LARGE_DESC = "We don't allow images larger than 5Mb - %s is %d bytes";
	const ERR_IMAGE_DUPLICATE_DESC = "%s has already been uploaded - duplicate images are not allowed";
	const ERR_IMAGE_NO_DATA = 'No image data supplied';

	const ERR_CATEGORY_NOT_INITIALIZED_DESC = 'Object is not initialized for this operation' ;
	const ERR_NO_CATEGORIES_FOUND_DESC = 'No categories found' ;

	const ERR_CONTEST_NOT_INITIALIZED_DESC = 'Object is not initialized for this operation' ;
	const ERR_NO_CONTEST_FOUND_DESC = "We can't locate a contest with ID '%d'" ;

	const ERR_PROJECTCOLLECTION_NOT_INITIALIZED_DESC = 'Object is not initialized for this operation' ;
	const ERR_NO_PROJECTCOLLECTION_FOUND_DESC = "Cannot add non-project class object to collection" ;

	const ERR_SUPPLYCOLLECTION_NOT_INITIALIZED_DESC = 'Object is not initialized for this operation' ;
	const ERR_NO_SUPPLYCOLLECTION_FOUND_DESC = "No suppliers found for item %" ;

	const ERR_SUPPLY_NOT_FOUND_DESC = 'Supplier id %d not found';
	const ERR_SUPPLY_NOT_CREATED_DESC = 'Supply could not be created';
	const ERR_SUPPLY_NOT_INITIALIZED_DESC = 'Object is not initialized for this operation';
	const ERR_SUPPLY_NOT_UPDATED_DESC = "Can't update field '%s' with value '%s'";

	const ERR_COMMENT_NOT_FOUND_DESC = 'Comment id %d not found';
	const ERR_COMMENT_NOT_INITIALIZED_DESC = 'Object is not initialized for this operation';

	const ERR_COMMENTCOLLECTION_NOT_INITIALIZED_DESC = 'Object is not initialized for this operation';
	const ERR_NO_COMMENTCOLLECTION_FOUND_DESC = "No comment found for item %" ;

	const ERR_TANDC_NOT_FOUND_DESC = 'Terms and Conditions document id %d not found';
	const ERR_TANDC_NOT_INITIALIZED = 'Object is not initialized for this operation';

	const ERR_MAIL_ERROR_SENDING = 'Error sending mail to %s';

	public function __construct() {;
	}

	public function  __destruct() {
	}
}
?>