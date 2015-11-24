# simplest_cms_in_the_west
The Simplest CMS in the west uses either SQLite or MySQL to run, the default is SQLite.
We encountered a problem using Ubuntu Linux where the directory:

site.com/inc/db/small.sqlite  <-- the database file
We needed to change permissions to any person access so PHP could use the file, there is 
probably a permission you need to set chmod 777 or lower permissions.
