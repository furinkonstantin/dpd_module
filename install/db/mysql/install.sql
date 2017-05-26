create table b_dpdext_states (
    ID int not null auto_increment,
    dpdOrderNr text null,
	dpdParcelNr text null,
	pickupDate text null,
	planDeliveryDate text null,
	newState text null,
	transitionTime text null,
	terminalCode text null,
	terminalCity text null,
	consignee text null,	
	                              
    primary key (ID)
);