<?php

# constant from cad_registry.vocabulary table
define("ENTITY_METABOLITE", 11);
define("ENTITY_PROTEIN", 73);
define("ENTITY_REACTION", 77);

define("FASTA_PROTEIN", 74);
define("FASTA_NUCLEOTIDE", 75);

define("RXN_LEFT", 78);
define("RXN_RIGHT", 79);

define("EC_NUMBER", 76);

define("DB_CAS", 1);
define("DB_PUBCHEM", 2);
define("DB_CHEBI", 3);
define("DB_HMDB", 4);
define("DB_LIPIDMAPS", 5);
define("DB_KEGG", 6);
define("DB_BIOCYC", 7);
define("DB_MESH", 8);
define("DB_WIKIPEDIA", 9);
define("DB_REFMET", 0);
define("DB_DRUGBANK", 10);
define("DB_CLASSYFIRE", 0);

function enum_to_dbnames($ids) {
    return array_map(function($id) {
        switch($id) {
            case DB_HMDB: return "HMDB";
            case DB_KEGG: return "KEGG";
            case DB_MESH: return "NCBI MeSH";
            case DB_CHEBI: return "ChEBI";
            case DB_CAS: return "CAS Registry Number";
            case DB_BIOCYC: return "BioCyc";
            case DB_REFMET: return "RefMet";
            case DB_PUBCHEM: return "PubChem";
            case DB_DRUGBANK: return "DrugBank";
            case DB_LIPIDMAPS: return "LipidMaps";
            case DB_WIKIPEDIA: return "Wikipedia";

            default:
                return "";
        }
    }, $ids);
}