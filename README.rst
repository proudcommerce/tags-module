Tags module (OE Tags)
=====================

In the shop products can be tagged and those tags can then be used for shop search, seo and
a tag cloud for visualization in the shop frontend. The admin has complete control over the tags,
a logged in user can add new tags to an article.


Installation
------------

- Make a new folder "oetags" in the **modules/oe/** directory of your shop installation. Download https://github.com/OXIDprojects/tags-module/archive/master.zip and unpack it into this folder. **OR**
- Git clone the module to your OXID eShop **modules/oe/** directory:

  .. code:: bash

     git clone https://github.com/OXIDprojects/tags-module.git oetags

- Activate the module in administration panel. The module clears the tmp folder on installation.
- IMPORTANT: regenerate the views after module installation.

Uninstallation
--------------

Disable the module in administration panel and delete the module folder.
The module clears the tmp folder on installation. The module did add multilanguage fields to table oxartextends on installation
(oxartextends.OETAGS, oxartextends_OETAGS_1 etc. depending on you shop's language configuaration).
Delete those manually if needs be as those columns will not be deleted automatically on module deactivation.
Means in case you only plan to switch off the module temporarily tags data will still be available on netx module activation.

Important for EE with varnish
-----------------------------
In case you run the module on an EE with reverseProxy enabled (varnish cache) you'll need to install a module addon
named OE Tags EE ReverseProxy Addon.
This addon module currently resides in a separate branch in OXID-eSales/tags_module repository.
To install, make a new folder "oetags_ee" in the **modules/oe/** directory of your shop installation. Download https://github.com/OXIDprojects/tags-module/archive/EE_addon.zip and unpack it into this folder. **OR**
- Git clone the module to your OXID eShop **modules/oe/** directory:

  .. code:: bash

     git clone https://github.com/OXIDprojects/tags-module.git oetags_ee
     cd oetags_ee
     git checkout EE_addon

- Activate the module alongside the OE Tags module. Flush varnish cache and clear tmp directory manually.
- Deactivate when you deactivate the OE Tags module.

License
-------

Licensing of the software product depends on the shop edition used. The software for OXID eShop Community Edition
is published under the GNU General Public License v3. You may distribute and/or modify this software according to
the licensing terms published by the Free Software Foundation. Legal licensing terms regarding the distribution of
software being subject to GNU GPL can be found under http://www.gnu.org/licenses/gpl.html. The software for OXID eShop
Professional Edition and Enterprise Edition is released under commercial license. OXID eSales AG has the sole rights to
the software. Decompiling the source code, unauthorized copying as well as distribution to third parties is not
permitted. Infringement will be reported to the authorities and prosecuted without exception.

