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

    # Class description
    detailedDescriptionXml = compounddef.find('detaileddescription')
    detailedDescriptionText = paras2rst(detailedDescriptionXml).strip();
    if detailedDescriptionText != "":
      rst += detailedDescriptionText + "\n\n"

    # TODO a small table.
    # Namespace
    # All implemented interfaces
    #

    # Class name
    if compounddef.attrib['kind'] == "interface":
      rst += ".. php:interface:: " + title + "\n\n" 
    else:
      rst += ".. php:class:: " + title + "\n\n"

    # Methods
    for section in compounddef.iter('sectiondef'):
        kind = section.attrib['kind']
        print("  " + kind)
        if kind == "public-func":
            for member in section.iter('memberdef'):
                rst += methodXmlToRst(member, 'method')
        elif kind == "public-static-func":
            for member in section.iter('memberdef'):
                rst += methodXmlToRst(member, 'staticmethod')
        else:
            print("    Skipping, no rules to print this section")
    return rst

def methodXmlToRst(member, methodType):
    rst = ""
    documentedParams = {}
    dd = member.find('detaileddescription')
    
    params = dd.find('*/parameterlist')
    if params != None:
        # Use documented param list if present
        for arg in params.iter('parameteritem'):
            argname = arg.find('parameternamelist')
            argnameType = argname.find('parametertype').text
            argnameName = argname.find('parametername').text
            argdesc = arg.find('parameterdescription')
            argdescPara = argdesc.find('para').text
            documentedParams[argnameName] = ("    :param " + itsatype(argnameType)).rstrip() + " " + argnameName + ": " + argdescPara.rstrip() + "\n"

    methodName = member.find('definition').text.split("::")[-1]
    # TODO re-write argsString so that ", $foo = bar" shows as  " [, $foo]", and return type is included
    argsString = member.find('argsstring').text
    rst += "  .. php:" + methodType + ":: " + methodName + " " + argsString + "\n\n"

    # Member description
    mDetailedDescriptionText = paras2rst(dd).strip();
    if mDetailedDescriptionText != "":
      rst += "    " + mDetailedDescriptionText + "\n\n"

    # Param list from the definition in the code and use
    # documentation where available, auto-fill where not.
    params = member.iter('param')
    if params != None:
      for arg in params:
        paramKey = arg.find('declname').text
        if paramKey in documentedParams:
          # TODO apend info about default value
          rst += documentedParams[paramKey]
        else:
          # Undocumented param
          paramName = paramKey
          xmldebug(arg)
          typeEl = arg.find('type')
          typeStr = "" if typeEl is None else typeEl.text
          rst += "    :param " + (itsatype(typeStr) + " " + paramName).strip() + ":\n"

    ret = dd.find('*/simplesect')
    if ret != None:
        paras = ret.iter('para')
        rst += "    :returns: " + paras2rst(paras).strip() + "\n"
    if (params != None) or (ret != None):
        rst += "\n"
    print("    " +  methodName + " " + argsString)
    return rst

def paras2rst(paras):
    return "\n".join([para2rst(x) for x in paras])

def xmldebug(inp):
    print(ET.tostring(inp, encoding='utf8', method='xml').decode())

def para2rst(inp):
    print(inp.tag)
    ret = "" if inp.text is None else inp.text
    for subtag in inp:
        print(subtag.tag)
        txt = subtag.text
        if subtag.tag == "parameterlist":
            continue
        if subtag.tag == "simplesect":
            continue
        if txt is None:
            continue
        if subtag.tag == "ref":
            txt = ":class:`" + txt + "`"
        ret += txt + ("" if subtag.tail == None else subtag.tail)
    return ret

def itsatype(inp):
    if inp == None:
        return ""
    if inp in ["", "int", "string", "array", "float", "double"]:
        return inp
    else:
        return ":class:`" + inp + "`"

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

