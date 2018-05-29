var jisushuju={
	init:function(){
		/*加载样式表*/
		var url='/plugins/jisushuju/skin/jisushuju.css?'+new Date().getTime();
		var link = document.createElement("link");
		link.rel = "stylesheet";
		link.type = "text/css";
		link.href = url;
		document.getElementsByTagName("head")[0].appendChild(link);
		var csstype="flo";
		/*
		fix 固定  flo 浮动
		默认浮动,不需要在页面添加容器
		使用固定的方式在页面容器中指定class="fix"
		*/
		var cont=$("#queryContext");
		if(cont.length<1)
		{
			$(document.body).append('<div id="queryContext" class="'+csstype+'"></div>'); 
			cont=$("#queryContext");
		}
		if($("#queryContextbg").length<1)
		{
			$(document.body).append('<div id="queryContextbg"></div>'); 
		}
		cont.hide();
		$("#queryContextbg").hide();
	},
	query:function(num,wltype){
		if(num.indexOf('<br>')!=-1)
		{
			num=num.split('<br>')[0];
		}
		var cont=$("#queryContext");
		if(cont.length<1)
		{
			$(document.body).append('<div id="queryContext"></div>'); 
			cont=$("#queryContext");
		}
		if(num.length<1||wltype.length<1)
		{
			shtml='快递单号或者物流类型为空';
			return;
		}
		Ajax.call('/plugins/jisushuju/jisushuju_post.php'
			, 'number='+num+'&com='+wltype, 
			function(result){
				var shtml='<div class="header"><div class="th"><h2>物流轨迹</h2><span>'+wltype+':'+num+'</span>';
				if(cont.attr("class")=="flo"){
					shtml+='<a class="close" href="#" onclick="jisushuju.close()"></a>';
				}
				shtml+='</div></div><div class="tbody">';
				if(parseInt(result.status))
				{
					shtml+='<div class="errmsg">';
					shtml+=result.msg;
					shtml+='，请登录极速数据 www.jisuapi.com, 联系技术支持</div>';
				}else{
					var data_list = result.result.list;
					var data_deliverystatus = result.result.deliverystatus; // 物流状态 1在途中 2派件中 3已签收 4派送失败(拒签等)
					if(data_list.length==0){
						shtml+='<div class="errmsg">单号暂无轨迹，请<a href="#" onclick="jisushuju.query("'+num+'","'+wltype+'")">刷新重试</a></div>';
					}else{
						shtml+='<table class="kd_tb"><thead><tr><th class="th" colspan="3">物流状态 ';
						if (data_deliverystatus == "1") {
							shtml+='在途中';
						}else if (data_deliverystatus == "2") {
							shtml+='派件中';
						}else if (data_deliverystatus == "3") {
							shtml+='已签收';
						}else if (data_deliverystatus == "4") {
							shtml+='派送失败(拒签等)';
						}else {
							shtml+='暂无';
						}
						shtml+='</th></tr></thead><tbody>';
						var tardata_time,tardata_status;
						var sortBy = function (field, rev, primer) {
						    rev = (rev) ? -1 : 1;
						    return function (a, b) {
						        a = a[field];
						        b = b[field];
						        if (typeof (primer) != 'undefined') {
						            a = primer(a);
						            b = primer(b);
						        }
						        if (a < b) { return rev * -1; }
						        if (a > b) { return rev * 1; }
						        return 1;
						    }
						};
						for(var i=0;i<data_list.length;i++) {
						    tardata_time=data_list[i].time;
						    var index = tardata_time.indexOf(" ");
						    var result_date = tardata_time.substring(0,index);
						    var result_time = tardata_time.substring(index+1);
						    var tardata_fulldate = result_date.split("-");
						    var tardata_fulltime = result_time.split(":");
						    data_list[i].time = new Date(tardata_fulldate[0], tardata_fulldate[1]-1, tardata_fulldate[2], tardata_fulltime[0], tardata_fulltime[1], tardata_fulltime[2]).Format("yyyy-MM-dd HH:mm:ss");
						}
						data_list.sort(sortBy('time', false, String));
						for(var i=0;i<data_list.length;i++)
						{
							tardata_time=data_list[i].time;
							tardata_status=data_list[i].status;
							switch(i)
							{
								case 0:
									shtml+='<tr><td class="td1"><b class="fir"/></td>';
								break;
								case data_list.length-1:
									shtml+='<tr><td class="td1"><b class="end"/></td>';
								break;
								default:
									shtml+='<tr><td class="td1"><b class="mid"/></td>';
								break;
							}
							if(i==data_list.length-1){
								shtml+='<td class="cur td2">';
							}else{
								shtml+='<td>';
							}
								shtml+= tardata_time + '</td>';
							if(i==data_list.length-1){
								shtml+='<td class="cur td4">'+tardata_status+'</td></tr>';
							}else{
								shtml+='<td class="td4">'+tardata_status+'</td></tr>';
							}

						}
						shtml+='</tbody></table>';
					}
				}
				shtml+='</div>';
				shtml+='<div class="footer"><div class="info"><a class="cp" href="http://www.jisuapi.com" target="_blank">极速数据</a>提供数据支持(以上信息由物流公司提供，如无跟踪信息或有疑问，请咨询对应的物流公司)</div></div>';
				cont.html(shtml);
				$("#queryContext").show();
				if(cont.attr("class")=="flo")
					$("#queryContextbg").show();
				else
					$("#queryContextbg").hide();
			} , 'POST', 'JSON');
	},
	close:function(){
		$("#queryContext").hide();
		$("#queryContextbg").hide();
	}
};
jisushuju.init();


//---------------------------------------------------  
// 日期格式化  
// 格式 YYYY/yyyy/YY/yy 表示年份  
// MM/M 月份  
// W/w 星期  
// dd/DD/d/D 日期  
// hh/HH/h/H 时间  
// mm/m 分钟  
// ss/SS/s/S 秒  
//---------------------------------------------------  
Date.prototype.Format = function(formatStr)   
{   
    var str = formatStr;   
    var Week = ['日','一','二','三','四','五','六'];  
  
    str=str.replace(/yyyy|YYYY/,this.getFullYear());   
    str=str.replace(/yy|YY/,(this.getYear() % 100)>9?(this.getYear() % 100).toString():'0' + (this.getYear() % 100));   
  
    str=str.replace(/MM/,(this.getMonth()+1)>9?this.getMonth().toString():'0' + (this.getMonth()+1));   
    str=str.replace(/M/g,(this.getMonth()+1));   
  
    str=str.replace(/w|W/g,Week[this.getDay()]);   
  
    str=str.replace(/dd|DD/,this.getDate()>9?this.getDate().toString():'0' + this.getDate());   
    str=str.replace(/d|D/g,this.getDate());   
  
    str=str.replace(/hh|HH/,this.getHours()>9?this.getHours().toString():'0' + this.getHours());   
    str=str.replace(/h|H/g,this.getHours());   
    str=str.replace(/mm/,this.getMinutes()>9?this.getMinutes().toString():'0' + this.getMinutes());   
    str=str.replace(/m/g,this.getMinutes());   
  
    str=str.replace(/ss|SS/,this.getSeconds()>9?this.getSeconds().toString():'0' + this.getSeconds());   
    str=str.replace(/s|S/g,this.getSeconds());   
  
    return str;   
}   