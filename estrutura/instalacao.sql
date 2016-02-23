create table solicitacaorepasse (
  sequencial serial not null primary key,
  unidade_anousu integer not null,
  unidade_orgao integer not null,
  unidade_codigo integer not null,
  recurso integer not null,
  anexo integer not null,
  valor numeric not null,
  conta integer not null,
  data date not null,
  motivo text not null
);

create table solicitacaorepasseempnota (
  sequencial serial not null primary key,
  solicitacaorepasse integer not null,
  empnota integer not null,
  constraint solicitacaorepasse_sequencial_fk foreign key (solicitacaorepasse) references solicitacaorepasse
);

create index solicitacaorepasseempnota_solicitacaorepasse_in ON solicitacaorepasseempnota(solicitacaorepasse);

create table autorizacaorepasse (
  sequencial         serial  not null primary key,
  slip               integer not null,
  solicitacaorepasse integer not null  references solicitacaorepasse(sequencial),
  estornado          boolean not null default false
);

create index autorizacaorepasse_solicitacaorepasse_in ON autorizacaorepasse(solicitacaorepasse);
