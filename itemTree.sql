INSERT INTO item_tree (item_id, item_name) VALUES
('20eca463-47f1-401c-b978-bfd2eb68548c', 'Essential'),
('b41df440-fb36-4936-b289-caca4965762a', 'Nonessential');

INSERT INTO item_tree (item_id, item_name, parent_id) VALUES
--Essential
('32fb6db8-5af9-49c9-9da0-43628299d7af', 'Food', '20eca463-47f1-401c-b978-bfd2eb68548c'),
('4211f5a8-604d-4b5e-aa2b-ed0f089f12c9', 'Clothes', '20eca463-47f1-401c-b978-bfd2eb68548c'),
('96b02b76-46e1-410c-9005-047cbfde5ce9', 'Hygiene', '20eca463-47f1-401c-b978-bfd2eb68548c'),
('7f7d4d2f-9e56-4729-bf2f-ee75e77822a6', 'Data and Airtime', '20eca463-47f1-401c-b978-bfd2eb68548c'),
--Nonessential
('417e3621-cedc-4d57-a28a-cf1006f55698', 'Electronics', 'b41df440-fb36-4936-b289-caca4965762a'),
('6becd6c9-5102-4258-81bd-27e83703db2b', 'Decor', 'b41df440-fb36-4936-b289-caca4965762a'),
('90c24c5b-4df0-4ba5-9cf1-d48ff26ec54c', 'Recreation', 'b41df440-fb36-4936-b289-caca4965762a'),
('19d1639a-c66e-4a67-8e02-b528262e5b51', 'Furniture', 'b41df440-fb36-4936-b289-caca4965762a'),
('78c3139d-09d5-4ccc-86d8-44e548a656ac', 'Household and cleaning', 'b41df440-fb36-4936-b289-caca4965762a'),
('118faf2b-a085-4a80-8eec-065e173c603a', 'Drinks', 'b41df440-fb36-4936-b289-caca4965762a');


INSERT INTO item_tree (item_name, parent_id) VALUES
-- Food
    ('Nonperishable food', '32fb6db8-5af9-49c9-9da0-43628299d7af'),
    ('Perishable food', '32fb6db8-5af9-49c9-9da0-43628299d7af'),
    ('Frozen food', '32fb6db8-5af9-49c9-9da0-43628299d7af'),
    ('Readymade meals', '32fb6db8-5af9-49c9-9da0-43628299d7af'),
-- Clothes
    ('Summer clothes', '4211f5a8-604d-4b5e-aa2b-ed0f089f12c9'),
    ('Winter clothes', '4211f5a8-604d-4b5e-aa2b-ed0f089f12c9'),
-- Hygiene
    ('Soap', '96b02b76-46e1-410c-9005-047cbfde5ce9'),
    ('Toothbrush', '96b02b76-46e1-410c-9005-047cbfde5ce9'),
    ('Toothpaste', '96b02b76-46e1-410c-9005-047cbfde5ce9'),
    ('Deodorant', '96b02b76-46e1-410c-9005-047cbfde5ce9'),
    ('Sanitary products', '96b02b76-46e1-410c-9005-047cbfde5ce9'),
    ('Sexual health', '96b02b76-46e1-410c-9005-047cbfde5ce9'),
    ('Toilet paper', '96b02b76-46e1-410c-9005-047cbfde5ce9'), 
-- Data+Airtime
    ('Vodacom airtime/data', '7f7d4d2f-9e56-4729-bf2f-ee75e77822a6'),
    ('MTN airtime/data', '7f7d4d2f-9e56-4729-bf2f-ee75e77822a6'),
    ('Telkom airtime/data', '7f7d4d2f-9e56-4729-bf2f-ee75e77822a6'),
    ('Other airtime/data', '7f7d4d2f-9e56-4729-bf2f-ee75e77822a6'),
-- Electronics
    ('Appliances', '417e3621-cedc-4d57-a28a-cf1006f55698'),
    ('Gadgets', '417e3621-cedc-4d57-a28a-cf1006f55698'),
    ('Cables', '417e3621-cedc-4d57-a28a-cf1006f55698'),
-- Decor
    ('Plants', '6becd6c9-5102-4258-81bd-27e83703db2b'),
    ('Bed and bath', '6becd6c9-5102-4258-81bd-27e83703db2b'),
-- Recreation
    ('Sports', '90c24c5b-4df0-4ba5-9cf1-d48ff26ec54c'),
    ('Hobbies', '90c24c5b-4df0-4ba5-9cf1-d48ff26ec54c'),
    ('Crafts', '90c24c5b-4df0-4ba5-9cf1-d48ff26ec54c'),
    ('Toys and Teddies', '90c24c5b-4df0-4ba5-9cf1-d48ff26ec54c'),
-- Furniture
    ('Indoor', '19d1639a-c66e-4a67-8e02-b528262e5b51'),
    ('Outdoor', '19d1639a-c66e-4a67-8e02-b528262e5b51'),
-- House+Clean
    ('Brushes, brooms, and mops', '78c3139d-09d5-4ccc-86d8-44e548a656ac'),
    ('Cleaning agents', '78c3139d-09d5-4ccc-86d8-44e548a656ac'),
-- Drinks
    ('Hot drinks', '118faf2b-a085-4a80-8eec-065e173c603a'),
    ('Carbonated drinks', '118faf2b-a085-4a80-8eec-065e173c603a'),
    ('Alcoholic drinks', '118faf2b-a085-4a80-8eec-065e173c603a');