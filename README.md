#Talis Aspire Integration for Moodle (2.4)

**Original version created at the University of Sussex by [Stuart Lamour](https://github.com/stuartlamour/talisaspire)**

This plugin adds an activity resource type to Moodle that allows users with the appropriate permissions to pick a reading list from the associated course in [Talis Aspire](http://talisaspire.com/) which is then displayed within the content of the course, linking each resource to it's description in Aspire.

The rationale for this approach, as adopted by Sussex University E-Learning team, is summarised on their [blog](http://blogs.sussex.ac.uk/elearningteam/2013/12/10/integrating-reading-lists-talis-aspire/) by Paolo Oprandi.

The fork was created to upgrade the implementation to moodle 2.4 and refactor to allow for future API releases by Talis.

##Why? Isn't there already a moodle integration?

The Talis Aspire product allows lecturers to create resource lists using a simple bookmarklet, and where items are held by the library they are referenced accordingly. The software also allows a workflow around ordering and acquisitions for library staff, and has a separate module for 'digital content', such as material scanned under a CLA license.

All of this is a useful way of working for staff, but the default moodle integration for student view simply provides a block within moodle. The block displays a list of reading lists associated with the course code. This is appropriate for some institutions, but in other circumstances a significant amount of work may have gone into consolidating the student experience within the VLE, and sending students off into another product, with it's own cognitive burden for familiarisation, and a switch in language etc, may conceivably be disruptive.

University of Sussex created the prototype for this plugin partly as feedback to Talis and other interested parties, an alternate deeper model for integration.

##Potential disadvantages

Talis have not as yet exposed a full API to support this type of integration. Though a json service call can be made for a set of lists associate with a module, the returned json contains a lot of unnecessary references, and the lists themselves, or the sections within the list cannot be called independently. This plugin uses a workaround, scraping the html page representing the lists to get this information from the table of contents.

This method is not ideal, because parsing the json and html is a relatively intensive operation and, in the initial 2.4 implementation at least, the data is collected as static html and saved into moodle, therefore not updated when the list is changed in Aspire*, negating many of the advantages of using the resource list system provided by Talis. Once Talis have extended the API to allow access to lists *and* sections via json, this plugin should be relatively easy to adapt, even in a live site.

* The html is updated when the *reading list* resource is saved - in the future it will probably make use of cron to update at intervals