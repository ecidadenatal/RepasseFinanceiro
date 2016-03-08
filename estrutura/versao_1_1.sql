create table devolucaosolicitacaorepasse(
  sequencial         serial  not null primary key,
  solicitacaorepasse integer not null references solicitacaorepasse(sequencial),
  slip               integer not null,
  valor              numeric not null
);

create index devolucaosolicitacaorepasse_solicitacaorepasse_in ON devolucaosolicitacaorepasse(solicitacaorepasse);

alter table solicitacaorepasseempnota add column estornado boolean not null default false;

alter table autorizacaorepasse drop column estornado;
