Readme
------

This is a simple notification module. It provides e-mail notifications to
members about updates and changes to the Drupal web site.

For advanced notifications like `changes to nodes or taxonomies, such as new 
comments in specific forums, or additions to some category of blog`, you might 
try subscriptions.module [#]_ [#]_.

.. [#] http://drupal.org/project/subscriptions
.. [#] http://cvs.drupal.org/viewcvs/contributions/modules/subscriptions/

Requirements
------------

This module requires the lastest version of the current Drupal CVS version and
a working Crontab.

Installation
------------

1. Create the SQL tables. This depends a little on your system, but the most
   common method is:
     mysql -u username -ppassword drupal < notify.mysql

2. Copy the notify.module to the Drupal modules/ directory.

3. Check the "settings and filters" in the administration and set the module
   settings to your liking. Note: e-mail updates can only happen as frequently
   as the crontab is setup to. Check your crontab settings.

4. To enable notifications go to "Your notifications" in the account block and
   set your settings there.

Author
------

Kjartan Mannes <kjartan@drop.org>

Wish list
--------

This is in no particular order.

- Filters on what to notify about.
- Options to get full text in mail (040917AX: works for nodes, but not yet for comments).
- Some way of detecting mail bounces.
- 040917AX: Easier customization of mail content and layout (eg. adding links for authors, formatting subject and body, adding dates, etc). I think of a template file that gets filled w/ variables from notify.module.
