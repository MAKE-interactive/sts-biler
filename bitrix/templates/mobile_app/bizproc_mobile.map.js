{"version":3,"sources":["bizproc_mobile.js"],"names":["BX","BizProcMobile","doTask","parameters","callback","silent","bitrix_sessid","app","showPopupLoader","text","BMAjaxWrapper","MobileAjaxWrapper","Wrap","method","dataType","url","message","data","json","hidePopupLoader","ERROR","alert","title","BXMobileApp","onCustomEvent","callback_failure","e","console","error","openTaskPage","taskId","event","target","tagName","toLowerCase","anchorNode","findParent","tag","className","hasClass","loadPageBlank","unique","renderLogMessage","logElement","newContent","updateId","wrapper","parentNode","innerHTML","querySelector","tasks","JSON","parse","getAttribute","userId","statusWaiting","statusYes","statusNo","statusOk","statusCancel","userStatus","getUserFromTask","task","i","l","USERS","length","user","STATUS","ID","btn","findChild","style","display","taskBlock","userStatusCls","userStatusBlock","statusBlock","setAttribute","renderLogMessages","scope","workflowId","newLogContent","items","querySelectorAll","rendered","itemWorkflowId","toString","itemContent","loadLogMessageCallback","ajax","WORKFLOW_ID","onsuccess","HTML","parseInt","Math","random","document","renderFacePhoto","users","displayedUser","onload","src","showDatePicker","format","input","type","pickerParams","value","d","Date","siteFormat","formatted","formatDate","UI","DatePicker","setParams","show","PreventDefault"],"mappings":"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","file":""}