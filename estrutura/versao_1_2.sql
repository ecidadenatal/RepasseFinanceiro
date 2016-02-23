alter table autorizacaorepasse alter column slip drop not null;
alter table autorizacaorepasse add column data date;

update autorizacaorepasse set data = (select k17_data from caixa.slip where k17_codigo = slip);

alter table autorizacaorepasse alter column data set not null;

alter table solicitacaorepasse add column tipo integer not null default 1;


