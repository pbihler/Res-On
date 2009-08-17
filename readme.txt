Res-On
------

    Interface to access academic examination results online 
    in a privacy-respecting way.

    Copyright (C) 2007,  Pascal Bihler, Institute of Informatics III, University of Bonn
    Contact: bihler@iai.uni-bonn.de

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.


System Requirements
-------------------

  * (Apache) Webserver, equipped with PHP Version 5.0.0 or later, php5-zip installed
  * MySQL database, Version 4.0.11 or later
  * GnuPG (GPG), Version 1.4.7 or later (optional, for strong result encryption)
  
Installation
------------

  # Extract the content of the archive to a directory of your webserver, which is
    accessible via http(s) from the outside (e.g. via http://www.example.com/reson).
    If you're updating from an older version of Res-On, just extract the archive
    content over the old installation.
    
  # Make sure, that the webserver's user can write into the subdirectories "config" 
    and "keys".
    
  # Call the "setup.php" page (e.g. http://www.example.com/reson/setup.php). This page
    helps with the initial installation setup, as well as later configurations.
    
    # The master password is required to protect your setup - do not forget!
    # To disable GPG encryption, just leave the path to the executable empty.
      If you want to use GPG encryption, please notice the remarks below.
      
  # On the next page, you can add a new project/exam. To repeat this step later
    (to add more projects/exams), visit "create_project.php"
    (e.g. http://www.example.com/reson/create_project.php)
  
  # The following page let you configure the just created project.
  
  # Now, make sure that only the webserver (and no other user) can read the files in
    the "config" subdir - you may even want to make it write-protected.
    
  # If you want to reset your master password, open the file config/local.php
    in any editor and remove the line stating Config::$master_password = '...';
    Then, visit immediately the setup-page to set a new master password.
  

Small User Guide
----------------

  * Student's story:
  
     # During the exam, she receives an extra sheet containing an personal R-Key 
       and a password.
     # She copies the R-Key (as well as her student id/matriculation number)
       to the examen's cover sheet and takes the password sheet with her.
     # After some days, she visits http://www.example.com/reson, enters her
       matriculation number/student id and the password and enjoys her result.
  
  * Teacher's story:
  
    # In preperation of the exam, the teacher asked the administrator to visit 
      http://www.example.com/reson/create_project.php and create a new project.
    # The teacher then visits http://www.example.com/reson/, clicks on "Admin",
      enters the project's passwort and generates new R-Keys, on for each student.
    # Using the "Generate PDF Handout"-Button, the teacher generates and prints the
      handouts, which are distributed together with the exam. on the exam's header,
      a field named "R-Key:" is prepared.
    # After the grading of the exam, the teacher visits the admin-page again to
      enter the data into the database by providing R-Key, student id/matriculation 
      number and a result-string for each student (e.g. by CVS import from an excel
      sheet)

GPG Remarks
-----------

While generating R-Keys with GPG encryption method, you cn speed up the process by
doing some other work in parallel, using your mouse and keyboard.

If you want to run the program on a headless Linux machine (that means, on a computer without
someone sitting in front of it using a mouse and keyboard), you might run out of random 
numbers when generating gpg keys (= Res-On doesn't respond anymore and the file
/proc/sys/kernel/random/entropy_avail contains a number < 1000).

A workaround (if you don't need a secure random generator on your machine for other means)
is described here: http://n0tablog.wordpress.com/2007/11/24/running-out-of-entropy-in-debian-etch/