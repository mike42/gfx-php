#!/usr/bin/env python3
"""
This script converts the doxygen XML output, which contains the API description,
and generates reStructuredText suitable for rendering with the sphinx PHP
domain.
"""

from collections import OrderedDict
import xml.etree.ElementTree as ET
import os

inpDir = 'xml'
outDir = '.'
rootNamespace = 'Mike42::GfxPhp'

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

# Walk the XML and extract all members of the given 'kind'
def classMemberList(compounddef, memberKind):
    return classMemberDict(compounddef, memberKind).values()

def classMemberDict(compounddef, memberKind):
    # Find items declared on this class
    ret = OrderedDict()
    for section in compounddef.iter('sectiondef'):
        kind = section.attrib['kind']
        if kind != memberKind:
            continue
        for member in section.iter('memberdef'):
            methodName = member.find('definition').text.split("::")[-1]
            ret[methodName] = member
    # Follow-up with items from base classes
    if ("private" in memberKind) or ("static" in memberKind):
        # Private methods are not accessible, and static methods should be
        # called on the class which defines them.
        return ret
    for baseClass in compounddef.iter('basecompoundref'):
        # TODO load XML and recurse
        compoundRef = baseClass.text
        #print(compoundRef)
    return ret

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
    # Base class
    # All implemented interfaces
    # All known sub-classes

    # Class name
    if compounddef.attrib['kind'] == "interface":
      rst += ".. php:interface:: " + title + "\n\n" 
    else:
      rst += ".. php:class:: " + title + "\n\n"

    # Methods
    methods = classMemberList(compounddef, 'public-func')
    print("  methods:")
    for method in methods:
        rst += methodXmlToRst(method, 'method')

    # Static methods
    methods = classMemberList(compounddef, 'public-static-func')
    print("  static methods:")
    for method in methods:
        rst += methodXmlToRst(method, 'staticmethod')

    return rst

def methodXmlToRst(member, methodType):
    rst = ""
    documentedParams = {}
    dd = member.find('detaileddescription')
    returnInfo = retInfo(dd)
    params = dd.find('*/parameterlist')
    if params != None:
        # Use documented param list if present
        for arg in params.iter('parameteritem'):
            argname = arg.find('parameternamelist')
            argnameType = argname.find('parametertype').text
            argnameName = argname.find('parametername').text
            argdesc = arg.find('parameterdescription')
            argdescPara = argdesc.iter('para')
            doco = ("    :param " + argnameType).rstrip() + " " + argnameName + ":\n"
            if argdescPara != None:
              doco += paras2rst(argdescPara, "      ")
            documentedParams[argnameName] = doco
    methodName = member.find('definition').text.split("::")[-1]
    argsString = methodArgsString(member)

    if returnInfo != None and returnInfo['returnType'] != None:
      argsString += " -> " + returnInfo['returnType']
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
        paramDefval = arg.find('defval')
        if paramKey in documentedParams:
          paramDoc = documentedParams[paramKey].rstrip()
          # Append a "." if the documentation does not end with one, AND we
          # need to write about the default value later.
          if paramDoc[-1] != "." and paramDoc[-1] != ":" and paramDefval != None:
            paramDoc += "."
          rst += paramDoc + "\n"
        else:
          # Undocumented param
          paramName = paramKey
          typeEl = arg.find('type')
          typeStr = "" if typeEl == None else para2rst(typeEl)
          rst += "    :param " + (unencapsulate(typeStr) + " " + paramName).strip() + ":\n"
        # Default value description
        if paramDefval != None:
          rst += "      Default: ``" + paramDefval.text + "``\n"
    # Return value
    if returnInfo != None:
        if returnInfo['returnType'] != None:
            rst += "    :returns: " + itsatype(returnInfo['returnType'], False) + " -- " + returnInfo['returnDesc'] + "\n"
        else:
            rst += "    :returns: " + returnInfo['returnDesc'] + "\n"
    if (params != None) or (returnInfo != None):
        rst += "\n"
    print("    " +  methodName + " " + argsString)
    return rst

def methodArgsString(member):
    params = member.iter('param')
    if params == None:
        # Main option is to use arg list from doxygen
        argList = member.find('argsstring').text
        return "()" if argList == None else argList
    # TODO re-write argsString so that ", $foo = bar" shows as  " [, $foo]", and return type is included
    requiredParamPart = []
    optionalParamPart = []
    optionalSwitch = False
    for param in params:
        paramName = param.find('declname').text
        typeEl = param.find('type')
        typeStr = "" if typeEl == None else para2rst(typeEl)
        typeStr = unencapsulate(typeStr)
        paramStr = (typeStr + " " + paramName).strip()
        if param.find('defval') != None:
            optionalSwitch = True
        if optionalSwitch:
            optionalParamPart.append(paramStr);
        else:
            requiredParamPart.append(paramStr);
    # Output arg list as string according to sphinxcontrib-phpdomain format
    if len(requiredParamPart) > 0:
        if len(optionalParamPart) > 0:
            # Both required and optional args
            return "(" + ", ".join(requiredParamPart) + "[, " + ", ".join(optionalParamPart) + "])"
        else:
            # Only required args
            return "(" + ", ".join(requiredParamPart) + ")"
    else:
        if len(optionalParamPart) > 0:
            # Only optional args
            return "([" + ", ".join(requiredParamPart) + "])"
        else:
            # Empty arg list!
            return "()"       

def unencapsulate(typeStr):
    # TODO extract type w/o RST wrapping
    if typeStr[0:8] == ":class:`":
        return (typeStr[8:])[:-1]
    return typeStr

def allPrimitives():
    # Scalar type keywords and things you find in documentation (eg. 'mixed')
    # http://php.net/manual/en/functions.arguments.php#functions.arguments.type-declaration
    return ["self", "bool", "callable", "iterable", "mixed", "int", "string", "array", "float", "double", "number"]

def retInfo(dd):
    ret = dd.find('*/simplesect')
    if ret == None:
        return None
    paras = ret.iter('para')
    desc = paras2rst(paras).strip()
    descPart = (desc + " ").split(" ")
    if descPart[0] in allPrimitives() or descPart[0][0:8] == ":class:`":

        return {'returnType': unencapsulate(descPart[0]), 'returnDesc': " ".join(descPart[1:]).strip()}
    # No discernable return type
    return {'returnType': None, 'returnDesc': desc}

def paras2rst(paras, prefix = ""):
    return "\n".join([prefix + para2rst(x) for x in paras])

def xmldebug(inp):
    print(ET.tostring(inp, encoding='utf8', method='xml').decode())

def para2rst(inp):
    ret = "" if inp.text == None else inp.text
    for subtag in inp:
        print(subtag.tag)
        txt = subtag.text
        if subtag.tag == "parameterlist":
            continue
        if subtag.tag == "simplesect":
            continue
        if txt == None:
            continue
        if subtag.tag == "ref":
            txt = ":class:`" + txt + "`"
        ret += txt + ("" if subtag.tail == None else subtag.tail)
    return ret

def itsatype(inp, primitivesAsLiterals = False):
    if inp == None:
        return ""
    if inp == "":
        return ""
    if inp in allPrimitives():
        if primitivesAsLiterals:
          return "``" + inp + "``"
        else:
          return inp
    else:
        return ":class:`" + inp + "`"

def compounddefByRefId(classRefId):
    xmlFilename = inpDir + '/' + classRefId + '.xml'
    #print("  Opening " + xmlFilename)
    cl = ET.parse(xmlFilename)
    return cl.getroot().find('compounddef')

def renderClassByRefId(classRefId, name):
    print("Processing class " + name)
    print("  refid is " + classRefId)
    compounddef = compounddefByRefId(classRefId)
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

