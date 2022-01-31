CREATE TABLE public.news
(
    new_codigo bigserial NOT NULL,
    new_title character varying,
    new_body character varying,
    new_body_html character varying,
    new_id integer,
    new_data character varying,
    new_autodata timestamp without time zone NOT NULL DEFAULT now(),
    new_atualizacao timestamp without time zone,
    PRIMARY KEY (new_codigo)
);

ALTER TABLE public.news
    OWNER to tibia;

ALTER TABLE public.news
    ADD UNIQUE (new_id);