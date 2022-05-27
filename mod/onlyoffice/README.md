# Moodle ONLYOFFICE Integration plugin

This plugin enables users to edit office documents from [Moodle](https://moodle.org/) using ONLYOFFICE Docs packaged as Document Server - [Community or Enterprise Edition](#onlyoffice-docs-editions).

## Features

The app allows to:

* Edit text documents, spreadsheets, and presentations.
* Co-edit documents in real-time: use two co-editing modes (Fast and Strict), the Track Changes mode, comments, and the built-in chat.

Supported formats:

* For viewing and editing: DOCX, XLSX, PPTX, TXT, CSV.
* For viewing only: PDF.
* For converting to Office Open XML formats: ODT, ODS, ODP, DOC, XLS, PPT, PPS, EPUB, RTF, HTML, HTM.

## Installing ONLYOFFICE Docs

You will need an instance of ONLYOFFICE Docs (Document Server) that is resolvable and connectable both from Moodle and any end clients (version 4.2.7 and later are supported for use with the plugin). ONLYOFFICE Document Server must also be able to POST to Moodle directly.

You can install free Community version of ONLYOFFICE Docs or scalable Enterprise Edition with pro features.

To install free Community version, use [Docker](https://github.com/onlyoffice/Docker-DocumentServer) (recommended) or follow [these instructions](https://helpcenter.onlyoffice.com/installation/docs-community-install-ubuntu.aspx) for Debian, Ubuntu, or derivatives.

To install Enterprise Edition, follow the instructions [here](https://helpcenter.onlyoffice.com/installation/docs-enterprise-index.aspx).

Community Edition vs Enterprise Edition comparison can be found [here](#onlyoffice-docs-editions).

## Installing Moodle ONLYOFFICE Integration plugin

This plugin is an __activity module__.

Follow the usual Moodle plugin installation steps to install this plugin into your __mod/onlyoffice__ directory. Please see [Moodle Documentation](https://docs.moodle.org/311/en/Installing_plugins) for more information.

The latest compiled package files are available [here](https://github.com/ONLYOFFICE/onlyoffice-moodle/releases).

## Configuring Moodle ONLYOFFICE Integration plugin

Once the plugin is installed, the plugin settings page will be opened. Alternatively, you can find the uploaded app on the `Plugins overview` page and click `Settings`.

Enter the name of the server with ONLYOFFICE Document Server installed in the **Document Editing Service address** field.

Enter the **Document Server Secret** to enable JWT protection of your documents from unauthorized access (further information can be found [here](https://api.onlyoffice.com/editors/signature/)).

## Using Moodle ONLYOFFICE Integration plugin

Once the plugin is installed and configured, you can add instances of ONLYOFFICE activity to your course pages as per usual Moodle practice:

1. Open the necessary course page.
2.  Activate the **Edit Mode** using the switcher at the top right corner.
3. Click **Add an activity or resource**.
4. Select the **ONLYOFFICE document** activity in the pop-up window.
5. Type in the activity name, upload or drag-and-drop the necessary document from your PC and click the **Save and display** button.

Admins/Teachers can choose whether or not documents can be downloaded or printed from inside the ONLYOFFICE editor. This can be done in the **Document permissions** section.

Clicking the activity name/link in the course page opens the *ONLYOFFICE editor* in the user's browser, ready for collaborative editing.

## ONLYOFFICE Docs editions

ONLYOFFICE offers different versions of its online document editors that can be deployed on your own servers. 

**ONLYOFFICE Docs** packaged as Document Server:

* Community Edition (`onlyoffice-documentserver` package)
* Enterprise Edition (`onlyoffice-documentserver-ee` package)

The table below will help you make the right choice.

| Pricing and licensing | Community Edition | Enterprise Edition |
| ------------- | ------------- | ------------- |
| | [Get it now](https://www.onlyoffice.com/download-docs.aspx#docs-community)  | [Start Free Trial](https://www.onlyoffice.com/download-docs.aspx#docs-enterprise)  |
| Cost  | FREE  | [Go to the pricing page](https://www.onlyoffice.com/docs-enterprise-prices.aspx)  |
| Simultaneous connections | up to 20 maximum  | As in chosen pricing plan |
| Number of users | up to 20 recommended | As in chosen pricing plan |
| License | GNU AGPL v.3 | Proprietary |
| **Support** | **Community Edition** | **Enterprise Edition** |
| Documentation | [Help Center](https://helpcenter.onlyoffice.com/installation/docs-community-index.aspx) | [Help Center](https://helpcenter.onlyoffice.com/installation/docs-enterprise-index.aspx) |
| Standard support | [GitHub](https://github.com/ONLYOFFICE/DocumentServer/issues) or paid | One year support included |
| Premium support | [Buy Now](https://www.onlyoffice.com/support.aspx) | [Buy Now](https://www.onlyoffice.com/support.aspx) |
| **Services** | **Community Edition** | **Enterprise Edition** |
| Conversion Service                | + | + |
| Document Builder Service          | + | + |
| **Interface** | **Community Edition** | **Enterprise Edition** |
| Tabbed interface                       | + | + |
| Dark theme                             | + | + |
| 150% scaling                           | + | + |
| White Label                            | - | - |
| Integrated test example (node.js)     | + | + |
| Mobile web editors | - | + |
| Access to pro features via desktop     | - | + |
| **Plugins & Macros** | **Community Edition** | **Enterprise Edition** |
| Plugins                           | + | + |
| Macros                            | + | + |
| **Collaborative capabilities** | **Community Edition** | **Enterprise Edition** |
| Two co-editing modes              | + | + |
| Comments                          | + | + |
| Built-in chat                     | + | + |
| Review and tracking changes       | + | + |
| Display modes of tracking changes | + | + |
| Version history                   | + | + |
| **Document Editor features** | **Community Edition** | **Enterprise Edition** |
| Font and paragraph formatting   | + | + |
| Object insertion                | + | + |
| Adding Content control          | - | + | 
| Editing Content control         | + | + | 
| Layout tools                    | + | + |
| Table of contents               | + | + |
| Navigation panel                | + | + |
| Mail Merge                      | + | + |
| Comparing Documents             | - | + |
| **Spreadsheet Editor features** | **Community Edition** | **Enterprise Edition** |
| Font and paragraph formatting   | + | + |
| Object insertion                | + | + |
| Functions, formulas, equations  | + | + |
| Table templates                 | + | + |
| Pivot tables                    | + | + |
| Data validation                 | + | + |
| Conditional formatting | + | + |
| Sheet Views                     | - | + |
| **Presentation Editor features** | **Community Edition** | **Enterprise Edition** |
| Font and paragraph formatting   | + | + |
| Object insertion                | + | + |
| Transitions                     | + | + |
| Presenter mode                  | + | + |
| Notes                           | + | + |
| | [Get it now](https://www.onlyoffice.com/download-docs.aspx#docs-community)  | [Start Free Trial](https://www.onlyoffice.com/download-docs.aspx#docs-enterprise)  |

In case of technical problems, the best way to get help is to submit your issues [here](https://github.com/ONLYOFFICE/onlyoffice-moodle/issues). Alternatively, you can contact ONLYOFFICE team on [forum.onlyoffice.com](https://forum.onlyoffice.com/).
