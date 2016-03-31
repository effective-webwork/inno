{#
<?php
#}
{{function('get_header')}}

{% block hiddenData %}
    {% for key, value in hiddenData %}
        {% if value is iterable %}
            {% for innerKey, innerValue in value.title %}
                <input type="hidden" name="hidden[{{key}}][title][]" value="{{innerValue}}"/>
            {% endfor %}
        {% else %}
            <input type="hidden" name="hidden[{{key}}]" value="{{value}}"/>
        {% endif %}
    {% endfor %}
{% endblock %}

<div id="columnset">
    <div id="content" role="main">
        {{function('fbpsych_get_rotator_image')}}

        <div id="left_column">
                    
            <div id="maincontent">

                {{function('fbpsych_get_step_image')}}

                {% if formStep == 'init' %}
                    <h1>{{__('FBP_PROJECT_TITLE_FIRST', 'twentytwelve')}}</h1>
                {% elseif formStep == 'template' %}
                    <h1>{{__('FBP_PROJECT_TITLE_FIRST', 'twentytwelve')}} &rsaquo; {{__('FBP_PROJECT_TITLE_TEMPLATE', 'twentytwelve')}}</h1>
                {% elseif formStep == 'teams' %}
                    <h1>{{__('FBP_PROJECT_TITLE_FIRST', 'twentytwelve')}} &rsaquo; {{__('FBP_PROJECT_TITLE_PARTICIPANTS', 'twentytwelve')}}</h1>
                {% elseif formStep == 'confirm' %}
                    <h1>{{__('FBP_PROJECT_TITLE_FIRST', 'twentytwelve')}} &rsaquo; {{__('FBP_PROJECT_TITLE_CONFIRM', 'twentytwelve')}}</h1>
                {% endif %}
                <div class="entry-content">
                    {% if formStep == 'init' %}
                        {{__('FBP_PROJECT_MAIN_WELCOME', 'twentytwelve')}}

                        <div class="create_survey">
                            <h2>{{__('FBP_PROJECT_CREATE_SURVEY', 'twentytwelve')}}</h2><br><br>

                            <a name="topOfForm"></a>
                            <form class="pure-form" name="createsurvey" action="?#topOfForm" method="post" enctype="multipart/form-data">
                                <fieldset>
                                    <input type="text" size="40" name="surveyName" placeholder="Titel">
                                    <select name="templateId">
                                        {% for blogSurvey in blogSurveys %}
                                            {% if blogSurvey.active == true %}
                                                <option value="{{blogSurvey.surveyId}}">{{blogSurvey.title}}</option>
                                            {% endif %}
                                        {% endfor %}
                                    </select>
                                    
                                    <input type="hidden" name="step" value="init" />

                                    <input class="pure-button" type="submit" name="next" id="submit-inline" value="weiter" />
                                </fieldset>
                            </form>
                                                           
                            <div class="clear"></div>
                        </div>
                    {% elseif formStep == 'template' %}
                        {{__('FBP_PROJECT_TEMPLATE', 'twentytwelve')}}

                        <div class="create_survey">
                            <div class="form-navigation">
                                <span><strong>Vorlage ausw&auml;hlen</strong></span> &gt;
                                <span>Teams anlegen</span> &gt;
                                <span>Übersicht</span>
                            </div>

                            <a name="topOfForm"></a>
                            <form class="pure-form pure-form-stacked" name="createsurvey" action="?#topOfForm" method="post" enctype="multipart/form-data">
                                <fieldset>
                                    <legend>{{__('FBP_PROJECT_TEMPLATE_SHORT', 'twentytwelve')}}</legend>

                                    <p>{{__('FBP_PROJECT_TEMPLATE_DESCRIPTION', 'twentytwelve')}}</p>

                                    <label for="template">{{__('FBP_PROJECT_TEMPLATE_SELECT', 'twentytwelve')}}</label>
                                    <select id="template" name="template">
                                        {% for surveyTemplate in surveyTemplates %}
                                            {% if formData.templateSelected == surveyTemplate.surveyId %}
                                                <option value="{{surveyTemplate.surveyId}}" selected>{{surveyTemplate.surveyName}}</option>
                                            {% else %}
                                                <option value="{{surveyTemplate.surveyId}}">{{surveyTemplate.surveyName}}</option>
                                            {% endif %}
                                        {% endfor %}
                                    </select>
                                    
                                    <input type="hidden" name="step" value="template" />

                                    {{ block('hiddenData') }}
                                    
                                    <input class="pure-button wide" type="submit" name="next" id="submit-secondary" value="aus Vorlage erstellen" />
                                    <input class="pure-button right wide" type="submit" name="next-skip" id="submit" value="weiter ohne Vorlage" />
                                </fieldset>
                            </form>
                                                           
                            <div class="clear"></div>
                        </div>
                    {% elseif formStep == 'teams' %}
                        {{__('FBP_PROJECT_COMPANY_DATA', 'twentytwelve')}}
                    
                        <div class="create_survey">
                            <div class="form-navigation">
                                {% if showTemplate %}
                                    <span><a href="?{{hiddenData|url_encode}}&step=template#topOfForm">Vorlage ausw&auml;hlen</a></span> &gt; 
                                {% endif %}
                                <span><strong>Teams anlegen</strong></span> &gt;
                                <span>Übersicht</span>
                            </div>

                            <a name="topOfForm"></a>
                            <form class="pure-form" name="createsurvey" action="?#topOfForm" method="post" enctype="multipart/form-data">
                                <!--  errors -->
                                <div class="formErrors">
                                    {% if formErrors.total %}
                                        {% if formErrors.total == 'empty' %}
                                            <div>{{__('FBP_PROJECT_ERROR_NO_TOTAL', 'twentytwelve')}}</div>
                                        {% endif %}
                                        {% if formErrors.total == 'numeric' %}
                                            <div>{{__('FBP_PROJECT_ERROR_TOTAL_NOT_NUMERIC', 'twentytwelve')}}</div>
                                        {% endif %}
                                        {% if formErrors.total == 'negativ' %}
                                            <div>{{__('FBP_PROJECT_ERROR_TOTAL_NEGATIV', 'twentytwelve')}}</div>
                                        {% endif %}
                                        {% if formErrors.total == 'limit' %}
                                            <div>{{__('FBP_PROJECT_ERROR_TOTAL_LIMIT', 'twentytwelve')}}</div>
                                        {% endif %}
                                    {% endif %}

                                    {% if formErrors.threshold %}
                                        {% if formErrors.threshold == 'numeric' %}
                                            <div>{{__('FBP_PROJECT_ERROR_TEAM_THRESHOLD_NUMERIC', 'twentytwelve')}}</div>
                                        {% endif %}
                                        {% if formErrors.threshold == 'minimums' %}
                                            <div>{{__('FBP_PROJECT_ERROR_TEAM_THRESHOLD_MINIMUMS', 'twentytwelve')}}</div>
                                        {% endif %}
                                    {% endif %}

                                    {% if formErrors.teams %}
                                        {% if 'empty title' in formErrors.teams %}
                                            <div>{{__('FBP_PROJECT_ERROR_NO_TEAM_TITLE', 'twentytwelve')}}</div>
                                        {% endif %}
                                    {% endif %}
                                </div>

                                <fieldset>
                                    <legend>{{__('FBP_PROJECT_PARTICIPANTS_SHORT', 'twentytwelve')}}</legend>

                                    <div>{{__('FBP_PROJECT_COUNT_TOTAL_DESC', 'twentytwelve')}}</div>

                                    <label for="survey_create_count_total">{{__('FBP_PROJECT_COUNT_TOTAL', 'twentytwelve')}}<span style="color: red;">*</span></label>
                                    <input id="survey_create_count_total" type="text" name="tokenCount" value="{% if formData.tokenCount %}{{formData.tokenCount}}{% endif %}" size="3"/>

                                    <div class="right">
                                        <img class="help" id="form_help_count_total" src="{{function('get_template_directory_uri')}}/img/help.png" />
                                    </div>
                                    <div data-dojo-type="dijit/Tooltip" data-dojo-props="connectId:'form_help_count_total', position:['above']">
                                        {{__('FBP_PROJECT_COUNT_TOTAL_HELP', 'twentytwelve')}}
                                    </div>
                                </fieldset>

                                {% if showTeams %}
                                    <fieldset>
                                        <legend>{{__('FBP_PROJECT_TEAMS_SHORT', 'twentytwelve')}}</legend>

                                        <div>{{__('FBP_PROJECT_TEAMS_DESCRIPTION', 'twentytwelve')}}</div>

                                        <button class="create_new" type="submit" name="addTeam" value="true">
                                            <img src="{{function('get_template_directory_uri')}}/img/add.png" /><span>{{__('FBP_PROJECT_CREATE_TEAM', 'twentytwelve')}} {{__('FBP_ADD', 'twentytwelve')}}</span>
                                        </button>


                                        <div class="right">
                                            <img class="help" id="form_help_team" src="{{function('get_template_directory_uri')}}/img/help.png" />
                                        </div>
                                        <div data-dojo-type="dijit/Tooltip" data-dojo-props="connectId:'form_help_team', position:['above']">
                                            {{__('FBP_PROJECT_TEAM_HELP', 'twentytwelve')}}
                                        </div>

                                        {% set newTeamIndex = 1 %}
                                        {% if formData.teams.title %}
                                            {% for title in formData.teams.title %}
                                                <div class="teamRow">
                                                    <div class="column_100">Team {{loop.index}}<span style="color: red;">*</span></div>
                                                    <div class="column_350">
                                                        <input class="text" type="text" name="teams[title][]" value="{{title|trim}}" size="50" />
                                                    </div>
                                                    <div class="column_100">
                                                        <button class="delete" type="submit" name="delTeam" value="{{loop.index0}}">
                                                            <img src="{{function('get_template_directory_uri')}}/img/delete.png" /><span>{{__('FBP_REMOVE', 'twentytwelve')}}</span>
                                                        </button>
                                                    </div>
                                                    <div class="clear"></div>
                                                </div>

                                                {% set newTeamIndex = loop.index + 1 %}
                                            {% endfor %}
                                        {% endif %}

                                        {% if formEvent.addTeam %}
                                            <div class="teamRow">
                                                <div class="column_100">Team {{newTeamIndex}}<span style="color: red;">*</span></div>
                                                <div class="column_350">
                                                    <input class="text" type="text" name="teams[title][]" size="50"/>
                                                </div>
                                                <div class="column_100">
                                                    <button class="delete" type="submit" name="delTeam" value="{{newTeamIndex - 1}}">
                                                        <img src="{{function('get_template_directory_uri')}}/img/delete.png" /><span>{{__('FBP_REMOVE', 'twentytwelve')}}</span>
                                                    </button>
                                                </div>
                                                <div class="clear"></div>
                                            </div>
                                        {% endif %}
                                    </fieldset>
                                {% endif %}
                                
                                <input type="hidden" name="step" value="teams" />

                                {{ block('hiddenData') }}

                                <input class="pure-button" type="submit" name="next" id="submit" value="weiter" />
                            </form>
                            
                            <div class="clear"></div>
                        </div>
                    {% elseif formStep == 'confirm' %}
                        {{__('FBP_PROJECT_CONFIRM', 'twentytwelve')}}

                        <div class="create_survey">
                            <div class="form-navigation">
                                {% if showTemplate %}
                                    <span><a href="?{{hiddenData|url_encode}}&step=template#topOfForm">Vorlage ausw&auml;hlen</a></span> &gt; 
                                {% endif %}
                                <span><a href="?{{hiddenData|url_encode}}&step=teams#topOfForm">Teams anlegen</a></span> &gt;
                                <span><strong>Übersicht</strong></span>
                            </div>

                            <a name="topOfForm"></a>
                            <form class="pure-form" name="createsurvey" action="?#topOfForm" method="post" enctype="multipart/form-data">
                                <fieldset>
                                    <legend>{{__('FBP_PROJECT_CONFIRM_SHORT', 'twentytwelve')}}</legend>

                                    <div>{{__('FBP_PROJECT_CONFIRM_DESCRIPTION', 'twentytwelve')}}</div>

                                    <div class="row left">
                                        <div class="column_250">{{__('FBP_PROJECT_CONFIRM_FORM_TITLE', 'twentytwelve')}}</div>
                                        <div class="left">{{formData.surveyName}}</div>

                                        <div class="clear"></div>
                                    </div>
                                    <div class="clear"></div>

                                    <div class="row left">
                                        <div class="column_250">{{__('FBP_PROJECT_CONFIRM_FORM_COUNT', 'twentytwelve')}}</div>
                                        <div class="left">{{formData.tokenCount}}</div>

                                        <div class="clear"></div>
                                    </div>
                                    <div class="clear"></div>

                                    {% if formData.teams.title %}
                                        <div class="row left">
                                            <div class="column_250">{{__('FBP_PROJECT_CONFIRM_FORM_TEAMS', 'twentytwelve')}}</div>
                                            <div class="left">
                                                {% for teamTitle in formData.teams.title %}
                                                    <div>- {{teamTitle|trim}}</div>
                                                {% endfor %}
                                            </div>

                                            <div class="clear"></div>
                                        </div>
                                        <div class="clear"></div>
                                    {% endif %}

                                    <input type="hidden" name="step" value="confirm" />

                                    {{ block('hiddenData') }}

                                    <input type="submit" id="submit" name="finish" value="erstellen" style="margin-top: 10px;" />
                                    <input type="submit" id="submit" name="abort" value="abbrechen" style="margin-top: 10px; float: left;" />
                                </fieldset>
                            </form>

                            <div class="clear"></div>
                        </div>
                    {% endif %}
                </div>
            </div>
        </div>

    </div>

    {{function('get_sidebar')}}
    <div class="clear"></div>
</div>

{{function('get_footer')}}