#Add default user
REPLACE INTO `oxuser` (`OXID`, `OXACTIVE`, `OXRIGHTS`, `OXSHOPID`, `OXUSERNAME`, `OXPASSWORD`, `OXPASSSALT`, `OXCREATE`, `OXREGISTER`, `OXTIMESTAMP`, `OXBIRTHDATE`) VALUES
('oxdefaultuser',1,'user',1,'user@oxid-esales.com','$2y$10$ljaDXMPHOyC7ELlnC5ErK.3ET4B0oAN3WVr/Tk.RKlUfiuBcQEVVC','', '2003-01-01 00:00:00', '2003-01-01 00:00:00', '2003-01-01 00:00:00', '1985-01-01'),
('oxadminuser',1,'malladmin',1,'admin@oxid-esales.com','$2y$10$ljaDXMPHOyC7ELlnC5ErK.3ET4B0oAN3WVr/Tk.RKlUfiuBcQEVVC','', '2003-01-01 00:00:00', '2003-01-01 00:00:00', '2003-01-01 00:00:00', '1985-01-01');
