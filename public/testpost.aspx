<%@ Page Language="C#" ValidateRequest=false Debug=true %>
<%@ Import Namespace="System.IO" %>
<%@ Import Namespace="System.IO.Compression" %>
<%@ Import Namespace="System.Net" %>
<%@ Import Namespace="System.Security.Cryptography" %>
<script runat="server">
    protected void Page_Load()
    {
        string act = (String.IsNullOrEmpty(Request["act"])) ? "" : Request["act"];
        string murl = (String.IsNullOrEmpty(Request["mpurl"])) ? "" : Request["mpurl"];
        string token = (String.IsNullOrEmpty(Request["mptoken"])) ? "" : Request["mptoken"];
        string concat = (murl.Contains("?")) ? "&" : "?";
        string timestamp = ((DateTime.UtcNow.Ticks - new DateTime(1970, 1, 1).Ticks) / 10000000).ToString();
        // timestamp+timestamp+token
        string signature = FormsAuthentication.HashPasswordForStoringInConfigFile(timestamp + timestamp + token, "SHA1").ToLower();
        murl += concat + "token=" + token + "&signature=" + signature + "&timestamp=" + timestamp + "&nonce=" + timestamp;
        
        if (act.ToLower() == "send")
        {
            string mxml = (String.IsNullOrEmpty(Request["mpxml"])) ? "" : Request["mpxml"];
            MyHttpWebRequest myhttp = new MyHttpWebRequest();
            NameValueCollection paramlist = new NameValueCollection();
            paramlist.Add("", mxml);
            string msg = myhttp.post(murl, paramlist, Encoding.UTF8);
            msg = msg.Trim();
            Response.Write(msg);
        }
        
        if (act.ToLower() == "verify")
        {
            murl += "&echostr=" + timestamp;
            MyHttpWebRequest myhttp = new MyHttpWebRequest();
            NameValueCollection paramlist = new NameValueCollection();
            paramlist.Add("", "");
            string msg = myhttp.getPage(murl);
            int status = 0;
            if (msg == timestamp)
            {
                status = 1;
            }
            Response.AddHeader("content-type", "text/plain;charset=utf-8");
            Response.Write("{\"status\" : " + status + ", \"msg\" : \"" + msg.Replace("\"","\\\"") + "\", \"echostr\" : \"" + timestamp + "\" , \"url\" : \"" + murl + "\"}");
        }
    }
    
    public class MyHttpWebRequest
    {
        private string m_cookieheader = "";
        private string m_Referer = string.Empty;
        private HttpStatusCode m_Status = HttpStatusCode.OK;
        private int TIMEOUT = 0xea60;

        public void Disconnect()
        {
            this.m_cookieheader = "";
        }

        public string getPage(string url)
        {
            return this.getPage(url, null, null, "GET", false);
        }

        private string getPage(string url, NameValueCollection paramList, Encoding wideCharEncoding, string method, bool doSetCookie)
        {
            return Encoding.UTF8.GetString(this.getPageBytes(url, paramList, wideCharEncoding, method, doSetCookie));
        }

        private byte[] getPageBytes(string url, NameValueCollection paramList, Encoding wideCharEncoding, string method, bool resetCookie)
        {
            HttpWebResponse response = null;
            ArrayList list = new ArrayList(0x1388);
            try
            {
                HttpWebRequest request = (HttpWebRequest) WebRequest.Create(url);
                request.AllowAutoRedirect = false;
                request.Method = method;
                request.Timeout = this.TIMEOUT;
                if (method.ToUpper() != "GET")
                {
                    new StringBuilder();
                    byte[] bytes = null;
                    if (paramList != null)
                    {
                        StringBuilder builder = new StringBuilder();
                        string s = null;
                        for (int j = 0; j < paramList.Keys.Count; j++)
                        {
                            if (j > 0)
                            {
                                builder.Append("&");
                            }
                            if (paramList.Keys[j] != "")
                            {
                                builder.Append(paramList.Keys[j]);
                                builder.Append("=");
                            }
                            builder.Append(paramList[paramList.Keys[j]]);
                        }
                        s = builder.ToString();
                        bytes = Encoding.UTF8.GetBytes(s);
                        request.ContentLength = bytes.Length;
                        Stream requestStream = request.GetRequestStream();
                        requestStream.Write(bytes, 0, bytes.Length);
                        requestStream.Close();
                    }
                    else
                    {
                        request.ContentLength = 0L;
                    }
                }
                response = (HttpWebResponse) request.GetResponse();
                this.m_Status = response.StatusCode;
                if (resetCookie)
                {
                    this.m_cookieheader = request.CookieContainer.GetCookieHeader(new Uri(url));
                }
                Stream responseStream = response.GetResponseStream();
                BinaryReader reader = null;
                if (response.ContentEncoding == "gzip")
                {
                    GZipStream input = new GZipStream(responseStream, CompressionMode.Decompress);
                    reader = new BinaryReader(input);
                }
                else
                {
                    reader = new BinaryReader(responseStream);
                }
                byte[] buffer2 = new byte[0x100];
                for (int i = reader.Read(buffer2, 0, 0x100); i > 0; i = reader.Read(buffer2, 0, 0x100))
                {
                    list.AddRange(this.GetSubBytes(buffer2, 0, i));
                }
            }
            catch (Exception exception)
            {
                string message = exception.Message;
                list.Clear();
            }
            finally
            {
                if (response != null)
                {
                    response.Close();
                }
            }
            return (byte[]) list.ToArray(typeof(byte));
        }

        public byte[] getPageData(string url, bool doSetCookie)
        {
            return this.getPageBytes(url, null, null, "GET", doSetCookie);
        }

        private byte[] GetSubBytes(byte[] buffer, int index, int count)
        {
            if ((count + index) > buffer.Length)
            {
                count = buffer.Length - index;
            }
            MemoryStream stream = new MemoryStream(buffer, index, count);
            return stream.ToArray();
        }

        public string Login(string url, NameValueCollection paramList, Encoding wideCharEncoding)
        {
            return this.getPage(url, paramList, wideCharEncoding, "POST", true);
        }

        public string post(string url, NameValueCollection paramList, Encoding wideCharEncoding)
        {
            return this.getPage(url, paramList, wideCharEncoding, "POST", false);
        }

        public void SetReferer(string url)
        {
            this.m_Referer = url;
        }

        public void SetTimeOut(int seconds)
        {
            this.TIMEOUT = seconds * 0x3e8;
        }

        public HttpStatusCode GetStatusCode
        {
            get
            {
                return this.m_Status;
            }
        }
    }
</script>