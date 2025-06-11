-- SQL script to create a comptable user for GSB application
-- User: Dumoulin Alphonse (as specified in the requirements)

INSERT INTO utilisateur (
    id, 
    nom, 
    prenom, 
    username, 
    mdp, 
    mdp_clair, 
    adresse, 
    cp, 
    ville, 
    dateEmbauche, 
    timespan, 
    type_utilisateur
) VALUES (
    'c1',                         -- id: unique identifier for the comptable
    'Dumoulin',                   -- nom: last name
    'Alphonse',                   -- prenom: first name
    'adumoulin',                  -- username: login username
    MD5('comptable1234'),         -- mdp: hashed password (using MD5 as specified in requirements)
    'comptable1234',              -- mdp_clair: clear text password (for compatibility)
    '15 rue de la Comptabilité',  -- adresse: address
    '75000',                      -- cp: postal code
    'Paris',                      -- ville: city
    '2022-01-01',                 -- dateEmbauche: hire date
    UNIX_TIMESTAMP(),             -- timespan: current timestamp
    'comptable'                   -- type_utilisateur: user type (comptable)
); 