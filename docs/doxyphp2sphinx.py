#!/usr/bin/env python3
"""
This script converts the doxygen XML output, which contains the API description,
and generates reStructuredText suitable for rendering with the sphinx PHP
domain.
"""

import xml.etree.ElementTree as ET
import os

inpDir = 'xml'
outDir = '.'
rootNamespace = 'Mike42::ImagePhp'

def renderNamespaceByName(tree, namespaceName):
    root = tree.getroot()
    for child in root:
        if child.attrib['kind'] != 'namespace':
            # Skip non-namespace
            continue
        thisNamespaceName = child.find('name').text
        if thisNamespaceName != namespaceName:
            continue
        renderNamespaceByRefId(child.attrib['refid'], thisNamespaceName)

def renderNamespaceByRefId(namespaceRefId, name):
    print("Processing namespace " + name)
    print("  refid is " + namespaceRefId)
    prefix = rootNamespace + "::"
    isRoot = False
    if name == rootNamespace:
      isRoot = True
    elif not name.startswith(prefix):
      print("  Skipping, not under " + rootNamespace)
      return
    xmlFilename = inpDir + '/' + namespaceRefId + '.xml'
    print("  Opening " + xmlFilename)
    ns = ET.parse(xmlFilename)
    compound = ns.getroot().find('compounddef')
    # Generate some markup
    title = "API documentation" if isRoot else name[len(prefix):] + " namespace"

    parts = name[len(prefix):].split("::")
    shortnameIdx = "api" if isRoot else ("api/" + "/".join(parts[:-1] + ['_' + parts[-1]]).lower())
    shortnameDir = "api" if isRoot else ("api/" + "/".join(parts[:-1] + [parts[-1]]).lower())
    glob = "api/*" if isRoot else parts[-1].lower() + "/*"
    outfile = outDir + "/" + shortnameIdx + ".rst"
    if not os.path.exists(outDir + '/' + shortnameDir):
        os.mkdir(outDir + "/" + shortnameDir)

    print("  Page title will be '" + title + "'")
    print("  Page path will be  '" + outfile + "'")

    # TODO extract description of namespace from comments
    desc = compound.find('detaileddescription').text
    print("  Desc is ... '" + desc  + "'")

    with open(outfile, 'w') as nsOut:
        nsOut.write(title + "\n");
        nsOut.write("=" * len(title) + "\n")
        nsOut.write("""\n.. toctree::
   :glob:

   """ + glob + "\n\n" + desc + "\n")

    for node in compound.iter('innerclass'):
        clId = node.attrib['refid']
        clName = node.text
        renderClassByRefId(clId, clName)

    for node in compound.iter('innernamespace'):
        nsId = node.attrib['refid']
        nsName = node.text
        renderNamespaceByRefId(nsId, nsName)

def classXmlToRst(compounddef, title):
    rst = title + "\n"
    rst += "=" * len(title) + "\n\n"

    # Class name
    rst += ".. php:class:: " + title + "\n\n"

    # Methods
    for section in compounddef.iter('sectiondef'):
        kind = section.attrib['kind']
        print("  " + kind)
        if kind == "public-func":
            for member in section.iter('memberdef'):
                methodName = member.find('definition').text.split("::")[-1]
                argsString = member.find('argsstring').text
                rst += "  .. php:method:: " + methodName + " " + argsString + "\n\n"
                dd = member.find('detaileddescription')
                params = dd.find('*/parameterlist')
                if params != None:
                    for arg in params.iter('parameteritem'):
                        argname = arg.find('parameternamelist')
                        argnameType = argname.find('parametertype').text
                        argnameName = argname.find('parametername').text
                        argdesc = arg.find('parameterdescription')
                        argdescPara = argdesc.find('para').text
                        rst += "      :param " + argnameType + " " + argnameName + ": " + argdescPara.rstrip() + "\n"
                ret = dd.find('*/simplesect')
                if ret != None:
                    para = ret.find('para')
                    typer = para.find('ref')
                    txt = para.text
                    if typer != None:
                        if txt != None:
                            txt = ":class:`" + typer.text + "` " + txt
                        else:
                            txt = ":class:`" + typer.text + "` "
                    if txt == None:
                        txt = "mixed"
                    rst += ("      :returns: " + txt).rstrip() + "\n"
                if (params != None) or (ret != None):
                    rst += "\n"

        elif kind == "public-static-func":
            for member in section.iter('memberdef'):
                methodName = member.find('definition').text.split("::")[-1]
                argsString = member.find('argsstring').text
                rst += "  .. php:staticmethod:: " + methodName + " " + argsString + "\n\n"

            pass
        else:
            print("    Skipping, no rules to print this section")

    #rst +=  .. php:method:: setDate($year, $month, $day)
    return rst

def renderClassByRefId(classRefId, name):
    print("Processing class " + name)
    print("  refid is " + classRefId)
    xmlFilename = inpDir + '/' + classRefId + '.xml'
    print("  Opening " + xmlFilename)
    cl = ET.parse(xmlFilename)
    compounddef = cl.getroot().find('compounddef')
    prefix = rootNamespace + "::"
    parts = name[len(prefix):].split("::")
    shortname = "api/" + "/".join(parts).lower()
    outfile = outDir + "/" + shortname + ".rst"
    title = parts[-1]

    print("  Class title will be '" + title + "'")
    print("  Class path will be  '" + outfile + "'")
    classRst = classXmlToRst(compounddef, title)

    with open(outfile, 'w') as classOut:
      classOut.write(classRst)

tree = ET.parse(inpDir + '/index.xml')
renderNamespaceByName(tree, rootNamespace);

