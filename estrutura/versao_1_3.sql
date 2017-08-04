create table plugins.recebimentorepassecontas (instituicao_origem int, instituicao_destino int, conta_debito_origem int, conta_credito_destino int, cgm_instituicao_destino int);
alter table plugins.recebimentorepassecontas add primary key (instituicao_origem,instituicao_destino);
alter table plugins.recebimentorepassecontas add column cgm_favorecido int;
