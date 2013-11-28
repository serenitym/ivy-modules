Coding style applied in most of the code follows a bit set of PEAR coding style 
guidelines [1], with a few alterations:

1. Class methods naming

In PEAR, the naming convention is:

    function someFunction() 
    {
        // code here
    }

In Ivy, its format is:

    function Group_someFunction()
    {
        // this is a function related to the Group "family"
    }
    
`Group` is a logical function subset, such as `Set`, `Get`, `Fs`, `Db` and 
others.

Some real name examples are: 
* @function `Fs_writeTo`@ - translates to "filesystem - write - to", which means 
  it attempts to write some data into a file. All the filesystem related 
  functions will be prefixed with *Fs_*, thus enabling a faster visual code 
  navigation. We also tend to group them by family in the code, so it's easier 
  to debug a filesystem or a database problem.


2. Class naming

In PEAR, we have:

    class Package_ClassName
    {
    }

Ivy has a special automation process for classes autoloading and the class 
instantiation is heavily automated, so there are some basic guidelines to 
ensure your code will be able to run and do its job.

Every module (and some of the core classes) has three basic components:
* the base class
* the privileged class, accessible only by an authenticated session
* a local component, used to override class methods and properties for a 
  specific instance only

    class Ccore
    {
        // here goes all the class logic

        /*
         * Name is formed by prefixing the class with a C
         * 
         * All classes following this convention are automation-enabled. If 
         * a specific class is designed to be called exclusively manually,
         * then the "C" prefix doesn't matter anymore and the only naming
         * convention we care about is camelCase.
         * 
         * Note: we will transition towards CapitalizedCamelCase at some point,
         * so it's better to adopt it for any new components from now on.
         */
    }

    class ACcore
    {
        // here goes all the class logic

        /*
         * Name has an additional "A" prefix, preceding the automation prefix "C"
         *
         * This means it's a privileged class, only accesibile via administrative 
         * rights. Guest sessions cannot trigger the execution of code in here.
         * 
         * It MUST be located under a "ADMIN" directory within a component 
         * structure, as follows:
         * 
         * module
         *  +
         *  |-ADMIN
         *  |  \-ACmodule.php
         *  |-README
         *  \-Cmodule.php
         */
    }

    class CLcore
    {
        // here goes all the class logic

        /*
         * "CL" prefix stands for a local class that overrides the default 
         * behavior of the base class.
         */
    }

    class ACLcore
    {
        // here goes all the class logic

        /*
         * "ACL" prefix stands for a local privileged class that overrides the 
         * default behavior of the base administrative class.
         */
    }

