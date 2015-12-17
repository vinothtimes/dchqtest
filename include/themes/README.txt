

Download the Theme Customization Reference manual here:
http://www.jamit.com/JamitThemes.pdf

====================

How to create a new theme:

1. Create a new directory with the name of the theme (do not put spaces in the 
name and think of a very unique name)

2. Copy the files from the default/ directory to your new directory. You only 
need to copy the php files that you want to change. Also, copy the CSS files 
and the deafult/images/ directory to your new directory. You may also copy
the default/lang/ directory too.

- You do not need to copy all the php files, just the ones that you want
changed. See the 'classic' for an example.

3. Set the job board to your new theme in Main Config, Admin. Design Away

==================================================

Editing themes.

The template files are normal php files. PHP has some very powerful templating
features bilt in to the language by default. The most important feature is
the ability to switch from between HTML and PHP at any time, and have both
languages embedded in the same file.

By default, a php file is assumed to be just a normal text based HTML file!

To go in to PHP mode, a special tag is inserted like this: <?php and then
closed like this ?>

Anything between the <?php and ?> tags is php code. You can edit the code
between these tags freely using a text editor which supports php. (eg
EditPlus)

Anything outside the <?php and ?> tags is HTML code. It can be edited freely
using a text editor. 

=========================

Some notes

- Themes are in include/themes directory, each theme has a separate directory. 
- By default, the job board will use the files from the include/themes/default/ directory. 
- When creating a new theme, you only provide custom versions of the template files that you want to override from the default theme. (See include/themes/classic for an example)
- Template files are just normal php files. There is no need to learn any new templating language, and the speed of executing the template files is extremely fast as there is no need for additional parsing or compilation needed.
- Themes can be selected from the Main Config

Here is how to create a new theme:

1. Create a new directory in the include/themes directory and call it anything you like.

2. For each template file that you want to customzie, copy the default files from themes/default/ to your new theme directory. These files may be php or css files. Also, copy the entire themes/default/images/ directory to your new theme directory. 

3. Set your job board to use your new theme in Main Config. Edit your theme files.
