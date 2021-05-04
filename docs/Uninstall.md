---
title: "Removing Minimum Maximum Order By Country"
date: 2021-04-27T12:53:58+01:00
draft: false
---
## Remove installation
    
1. Delete the following files from YOURADMIN directory.
    - `YOURADMIN\min_max_order.php`
    - `YOURADMIN\includes\auto_loaders\config.min_max_order.php`
    - `YOURADMIN\includes\extra_datafiles\min_max_order.php`
    - `YOURADMIN\includes\init_includes\init_min_max_order.php`
    - `YOURADMIN\includes\installers\min_max_order` **Whole Directory**
    - `YOURADMIN\includes\languages\english\min_max_order.php`
    - `YOURADMIN\includes\languages\english\extra_definitions\min_max_order.php`  
    
1. Delete the files from your includes directory`
    - `includes\auto_loaders\config.min_max_order.php`
    - `includes\classes\observers\class.min_max_order.php`
    - `includes\extra_datafiles\min_max_order.php`
    - `includes\languages\english\extra_definitions\min_max_order.php`  

1. Run UninstallMinMaxOrder.sql  
This removes the admin entries created on instalation and deletes the min_max_order table  
    1. **Backup your database** before running script.
    1. Login to your zen cart admin  
    1. Use Tools > Install SQL Patches
    1. Copy the contents of UninstallMinMaxOrder.sql and paste into the text window  
    or  
    User the Browse button to load UninstallMinMaxOrder.sql into zen cart.
    1. Press send button to execute script  
