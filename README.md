NOTE!!!
USE "productionorders" as reference. CUSTOMERS CAN ONLY BUY in BULK. USE THE GIVEN RATIO.



TODO:

* RAW MATERIAL TRACKER - QUANTITY PRODUCED AND UPDATE FOR PROGRESS.
SHOULD BE ABLE TO UPDATE HOW MUCH QUANTITY ARE CURRENTLY PRODUCED.

* REQUEST CUSTOMER ORDER - the system will check for the "quantityproduced" column and if it's not below the given ratio(e.g can = 192) it can be sold.

FORM:
HOW MANY, LOCATION, PRODUCT IMAGE, QUANTITY, DATE

* Customer Order Tracker - JUST TRACK IF IT'S BEING DELIVERED OR NOT

* DELIVER PROCESSED PRODUCT - TRACK THE LOCATION OF THE PRODUCT.

* CRUD IN ADMIN FOR ADDING NEW PRODUCTS AND NEW TYPE OF MATERIAL.


Warehouse Operations = N/A To be discussed


TO FIX:

ASAP:

Employee Profile = No profile picture of employee
Email support = should email all the "admins" in the "employees" table for ticket support.
Registration = Fix the "Select Roles" It should only be employee. You cannot register as admin. TWO ROLES ONLY.
ADMIN SESSION CHECK = USE session_check.php since it has a function for determining if user is an "ADMIN OR NOT"
AFTER REGISTRATING, IT SHOULD BE AUTOMATICALLY AN ACTIVE USER.
ADMIN = index.php for admin should redirect to admin-login.php

CHANGES:
MATERIAL RATIO LOGIC
NEW SIDEBAR ITEMS

TO BE DELAYED:

INVENTORY MANAGEMENT SHOULD FETCH "PRODUCTS" INVENTORY NOT "RAW MATERIALS". CANNOT BE YET FINSIHED SINCE CUSTOMER ORDER REQUEST/TRACKING IS NOT YET FINISH.