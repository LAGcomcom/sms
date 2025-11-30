package com.example.smsforwarder.util
import android.content.Context
import androidx.security.crypto.EncryptedSharedPreferences
import androidx.security.crypto.MasterKey
class ConfigStore(ctx: Context){
  private val masterKey=MasterKey.Builder(ctx).setKeyScheme(MasterKey.KeyScheme.AES256_GCM).build()
  private val sp=EncryptedSharedPreferences.create(ctx,"cfg",masterKey,EncryptedSharedPreferences.PrefKeyEncryptionScheme.AES256_SIV,EncryptedSharedPreferences.PrefValueEncryptionScheme.AES256_GCM)
  fun getServer():String=sp.getString("server","")?:""
  fun getToken():String=sp.getString("token","")?:""
  fun getPhone():String=sp.getString("phone","")?:""
  fun getDeviceId():String{ val v=sp.getString("device_id",null); if(v!=null) return v; val id=java.util.UUID.randomUUID().toString(); sp.edit().putString("device_id",id).apply(); return id }
  fun save(server:String,token:String,phone:String){ sp.edit().putString("server",server).putString("token",token).putString("phone",phone).apply() }
  fun getOverlayAlpha():Float=sp.getFloat("overlay_alpha",0.8f)
  fun setOverlayAlpha(v:Float){ sp.edit().putFloat("overlay_alpha",v).apply() }
  fun getOverlayX():Int=sp.getInt("overlay_x",100)
  fun getOverlayY():Int=sp.getInt("overlay_y",200)
  fun setOverlayPos(x:Int,y:Int){ sp.edit().putInt("overlay_x",x).putInt("overlay_y",y).apply() }
  fun pushMessage(sender:String,content:String){ val arr=org.json.JSONArray(sp.getString("msgs","[]")); val obj=org.json.JSONObject(); obj.put("sender",sender); obj.put("content",content); obj.put("ts",System.currentTimeMillis()); val list=java.util.ArrayList<org.json.JSONObject>(); for(i in 0 until arr.length()){ list.add(arr.getJSONObject(i)) } list.add(0,obj); while(list.size>5) list.removeAt(list.size-1); val out=org.json.JSONArray(); list.forEach{ out.put(it) }; sp.edit().putString("msgs",out.toString()).apply() }
  fun getMessages():List<Pair<String,String>>{ val arr=org.json.JSONArray(sp.getString("msgs","[]")); val res=mutableListOf<Pair<String,String>>(); for(i in 0 until arr.length()){ val o=arr.getJSONObject(i); res.add(Pair(o.optString("sender"),o.optString("content"))) } return res }
}
