OE Tags EE ReverseProxy Addon
=============================

This module is not a standalone module. It requires the installation of the OE Tags module.
In case you run the OE Tags module on an EE with reverseProxy enabled (varnish cache)
you'll need to install this module in addition.

Installation
------------

- Make a new folder "oetags_ee" in the **modules/oe/** directory of your shop installation. Download https://github.com/OXID-eSales/tags_module/archive/EE_addon.zip and unpack it into this folder. **OR**
- Git clone the module to your OXID eShop **modules/oe/** directory:

  .. code:: bash

     git clone https://github.com/OXID-eSales/tags_module.git oetags_ee
     cd oetags_ee
     git checkout EE_addon

- Activate the module after installation of the OE Tags module. Flush varnish cache and clear tmp directory manually.

Uninstallation
--------------

Disable the module in administration panel and delete the module folder.

License
-------

Licensing of the software product depends on the shop edition used. The software for OXID eShop Community Edition
is published under the GNU General Public License v3. You may distribute and/or modify this software according to
the licensing terms published by the Free Software Foundation. Legal licensing terms regarding the distribution of
software being subject to GNU GPL can be found under http://www.gnu.org/licenses/gpl.html. The software for OXID eShop
Professional Edition and Enterprise Edition is released under commercial license. OXID eSales AG has the sole rights to
the software. Decompiling the source code, unauthorized copying as well as distribution to third parties is not
permitted. Infringement will be reported to the authorities and prosecuted without exception.

